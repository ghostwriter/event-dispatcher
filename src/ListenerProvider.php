<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Closure;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Contract\SubscriberInterface;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineTypeDeclarationsException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\Option\Contract\SomeInterface;
use Ghostwriter\Option\Some;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use const SORT_NUMERIC;
use function array_key_exists;
use function class_exists;
use function interface_exists;
use function is_array;
use function is_object;
use function is_string;
use function is_subclass_of;
use function krsort;
use function md5;
use function spl_object_hash;
use function sprintf;

/**
 * Maps registered Listeners, Providers and Subscribers.
 *
 * @template TEvent of object
 */
final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var int
     */
    private const LISTENERS = 0;

    /**
     * @var int
     */
    private const PROVIDERS = 1;

    /**
     * @var int
     */
    private const SUBSCRIBERS = 2;

    /**
     * Map of registered Listeners, Providers and Subscribers.
     *
     * @var array{0:array<string,array<int,array<string,SomeInterface>>>,1:array<class-string<ListenerProviderInterface>,ListenerProviderInterface>,2:array<class-string<SubscriberInterface>,SubscriberInterface>}
     */
    private array $data = [[], [], []];

    public function __construct(
        private ?ContainerInterface $container = null
    ) {
        $this->container ??= Container::getInstance();
    }

    /**
     * @param callable(TEvent):void $listener
     */
    public function addListener(
        callable $listener,
        int $priority = 0,
        ?string $event = null,
        ?string $id = null
    ): string {
        $event ??= $this->getEventType($listener);
        $id ??= $this->getListenerId($listener);

        if ('object' !== $event && ! class_exists($event) && ! interface_exists($event)) {
            throw new InvalidArgumentException(sprintf('Event "%s" cannot be found.', $event));
        }

        if (
            array_key_exists($event, $this->data[self::LISTENERS]) &&
            array_key_exists($priority, $this->data[self::LISTENERS][$event]) &&
            array_key_exists($id, $this->data[self::LISTENERS][$event][$priority])
        ) {
            throw new InvalidArgumentException(sprintf(
                'Duplicate Listener "%s" detected for "%s" Event.',
                $id,
                $event
            ));
        }

        $this->data[self::LISTENERS][$event][$priority][$id] = Some::create($listener);

        krsort($this->data[self::LISTENERS][$event], SORT_NUMERIC);

        return $id;
    }

    public function addListenerAfter(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string {
        $priority = null;
        $event ??= $this->getEventType($listener);
        $id ??= $this->getListenerId($listener);

        foreach ($this->data[self::LISTENERS][$event] as $listenerPriority => $listenerKey) {
            if (array_key_exists($id, $listenerKey)) {
                $priority = $listenerPriority;

                break;
            }
        }

        if (null === $priority) {
            throw new ListenerNotFoundException($listenerId);
        }

        return $this->addListener($listener, $priority - 1, $event, $id);
    }

    /**
     * @param callable(TEvent):void $listener
     */
    public function addListenerBefore(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string {
        $event ??= $this->getEventType($listener);
        $id ??= $this->getListenerId($listener);
        $priority = null;

        foreach ($this->data[self::LISTENERS][$event] as $listenerPriority => $listenerKey) {
            if (array_key_exists($id, $listenerKey)) {
                $priority = $listenerPriority;
                break;
            }
        }

        if (null === $priority) {
            throw new InvalidArgumentException(sprintf('Listener "%s" cannot be found.', $listenerId));
        }

        return $this->addListener($listener, $priority + 1, $event, $id);
    }

    public function addListenerService(string $event, string $listener, int $priority = 0, ?string $id = null): string
    {
        return $this->addListener(
            function (object $event) use ($listener): void {
                /** @var callable(object):void $callable */
                $callable = $this->container->get($listener);
                $callable($event);
            },
            $priority,
            $event,
            $id
        );
    }

    public function addListenerServiceAfter(
        string $listenerId,
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string {
        return $this->addListenerService($event, $listener, $priority, $id);
    }

    public function addListenerServiceBefore(
        string $listenerId,
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string {
        return $this->addListenerService($event, $listener, $priority, $id);
    }

    public function addProvider(ListenerProviderInterface $psrListenerProvider): void
    {
        $id = $psrListenerProvider::class;
        if (array_key_exists($id, $this->data[self::PROVIDERS])) {
            throw new InvalidArgumentException(sprintf(
                'ListenerProvider with ID "%s" has already been registered',
                $id
            ));
        }

        $this->data[self::PROVIDERS][$id] = $psrListenerProvider;
    }

    public function addSubscriber(SubscriberInterface $subscriber): void
    {
        if (array_key_exists($subscriber::class, $this->data[self::SUBSCRIBERS])) {
            throw new InvalidArgumentException(sprintf(
                'Subscriber with ID "%s" has already been registered',
                $subscriber::class
            ));
        }

        $subscriber($this);

        $this->data[self::SUBSCRIBERS][$subscriber::class] = $subscriber;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addSubscriberService(string $subscriber): void
    {
        if (! is_subclass_of($subscriber, SubscriberInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Subscriber with ID "%s" must implement %s.',
                $subscriber,
                SubscriberInterface::class
            ));
        }

        $this->addSubscriber($this->container->get($subscriber));
    }

    /**
     * Return relevant/type-compatible Listeners for the Event.
     *
     * @param TEvent $event an event for which to return the relevant listeners
     *
     * @return iterable<callable(TEvent):void> an iterable of callables type-compatible with $event
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->data[self::LISTENERS] as $type => $priorities) {
            if ('object' !== $type && ! $event instanceof $type) {
                continue;
            }

            /** @var array<int, int> $priorities */
            foreach ($priorities as $priority) {
                /** @var array<int, Some<callable(TEvent):void>> $priority */
                foreach ($priority as $listener) {
                    /** @var null|bool $stop */
                    $stop = yield $listener->unwrap();
                    if (true === $stop) {
                        // event propagation has stopped
                        return;
                    }
                }
            }
        }

        foreach ($this->data[self::PROVIDERS] as $provider) {
            yield from $provider->getListenersForEvent($event);
        }
    }

    public function removeListener(string $listenerId): void
    {
        foreach ($this->data[self::LISTENERS] as $event => $listeners) {
            foreach ($listeners as $priority => $listener) {
                if (array_key_exists($listenerId, $listener)) {
                    unset($this->data[self::LISTENERS][$event][$priority][$listenerId]);
                    return;
                }
            }
        }

        throw new InvalidArgumentException(sprintf('Listener "%s" cannot be found.', $listenerId));
    }

    public function removeProvider(string $providerId): void
    {
        if (array_key_exists($providerId, $this->data[self::PROVIDERS])) {
            unset($this->data[self::PROVIDERS][$providerId]);
            return;
        }

        throw new InvalidArgumentException(sprintf('ListenerProvider "%s" cannot be found.', $providerId));
    }

    public function removeSubscriber(string $subscriberId): void
    {
        if (array_key_exists($subscriberId, $this->data[self::SUBSCRIBERS])) {
            unset($this->data[self::SUBSCRIBERS][$subscriberId]);
            return;
        }

        throw new InvalidArgumentException(sprintf('Subscriber "%s" cannot be found.', $subscriberId));
    }

    /**
     * Resolves the class type of the first argument on a callable.
     *
     * @param callable(TEvent):void $listener
     *
     * @throws FailedToDetermineTypeDeclarationsException if $listener is missing type-declarations
     * @throws FailedToDetermineTypeDeclarationsException if $listener class does not exist
     */
    private function getEventType(callable $listener): string
    {
        try {
            $parameters = is_array($listener) ?
                (new ReflectionClass($listener[0]))->getMethod($listener[1])->getParameters() :
                (new ReflectionFunction(Closure::fromCallable($listener)))->getParameters();
        } catch (ReflectionException $reflectionException) {
            throw new FailedToDetermineTypeDeclarationsException(
                $reflectionException->getMessage(),
                $reflectionException->getCode(),
                $reflectionException
            );
        }

        if ([] === $parameters) {
            throw FailedToDetermineTypeDeclarationsException::missingFirstParameter();
        }

        $reflectionType = $parameters[0]->getType();

        if (! $reflectionType instanceof ReflectionType) {
            throw FailedToDetermineTypeDeclarationsException::missingTypeDeclarations($parameters[0]->getName());
        }

        if ($reflectionType instanceof ReflectionNamedType) {
            return $reflectionType->getName();
        }

        throw FailedToDetermineTypeDeclarationsException::invalidTypeDeclarations(
            $parameters[0]->getName(),
            $reflectionType instanceof ReflectionUnionType ? 'UnionType' : 'IntersectionType'
        );
    }

    /**
     * Derives a unique ID from the listener.
     *
     * @param callable(TEvent):void $listener
     */
    private function getListenerId(callable $listener): string
    {
        /** @var array{0:object|string,1:string}|object|string $listener */
        return match (true) {
            // Function callables are strings, so use that directly.
            is_string($listener) => $listener,
            /** @var object $listener */
            is_object($listener) => sprintf('listener.%s', md5(spl_object_hash($listener))),
            // Object callable represents a method on an object.
            is_object($listener[0]) => sprintf('%s::%s', $listener[0]::class, $listener[1]),
            // Class callable represents a static class method.
            default => sprintf('%s::%s', $listener[0], $listener[1])
        };
    }
}
