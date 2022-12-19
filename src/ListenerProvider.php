<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Closure;
use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Contract\SubscriberInterface;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineTypeDeclarationsException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\Option\Contract\SomeInterface;
use Ghostwriter\Option\Some;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
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
use function spl_object_hash;
use function sprintf;

/**
 * Maps registered Listeners, Providers and Subscribers.
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
     * @param callable(EventInterface):void            $listener
     * @param null|class-string<EventInterface>|string $event
     */
    public function addListener(
        callable $listener,
        int $priority = 0,
        ?string $event = null,
        ?string $id = null
    ): string {
        $id ??= $this->getListenerId($listener);
        $events = $this->getEventType($listener, $event);
        foreach ($events as $eventType) {
            if ('object' !== $eventType && ! class_exists($eventType) && ! interface_exists($eventType)) {
                throw new InvalidArgumentException(sprintf('Event "%s" cannot be found.', $eventType));
            }

            if (
                array_key_exists($eventType, $this->data[self::LISTENERS]) &&
                array_key_exists($priority, $this->data[self::LISTENERS][$eventType]) &&
                array_key_exists($id, $this->data[self::LISTENERS][$eventType][$priority])
            ) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate Listener "%s" detected for "%s" Event.',
                    $id,
                    $eventType
                ));
            }

            $this->data[self::LISTENERS][$eventType][$priority][$id] = Some::create($listener);

            krsort($this->data[self::LISTENERS][$eventType], SORT_NUMERIC);
        }

        return $id;
    }

    public function addListenerAfter(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string {
        $priority = null;
        $id ??= $this->getListenerId($listener);
        $events = $this->getEventType($listener, $event);
        foreach ($events as $eventType) {
            foreach ($this->data[self::LISTENERS][$eventType] as $listenerPriority => $listenerKey) {
                if (array_key_exists($id, $listenerKey)) {
                    $priority = $listenerPriority;

                    break;
                }
            }

            if (null === $priority) {
                throw new ListenerNotFoundException($listenerId);
            }

            return $this->addListener($listener, $priority - 1, $eventType, $id);
        }
        return $this->addListener($listener, $priority - 1, $event, $id);
    }

    /**
     * @param callable(EventInterface):void $listener
     */
    public function addListenerBefore(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string {
        $priority = null;
        $id ??= $this->getListenerId($listener);
        $events = $this->getEventType($listener, $event);
        foreach ($events as $eventType) {
            foreach ($this->data[self::LISTENERS][$eventType] as $listenerPriority => $listenerKey) {
                if (array_key_exists($id, $listenerKey)) {
                    $priority = $listenerPriority;
                    break;
                }
            }

            if (null === $priority) {
                throw new InvalidArgumentException(sprintf('Listener "%s" cannot be found.', $listenerId));
            }

            return $this->addListener($listener, $priority + 1, $eventType, $id);
        }
        return $this->addListener($listener, $priority + 1, $event, $id);
    }

    public function addListenerService(
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string {
        return $this->addListener(
            fn (EventInterface $eventInterface): mixed => $this->container->get($listener)($eventInterface),
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

    public function addProvider(ListenerProviderInterface $listenerProvider): void
    {
        $id = $listenerProvider::class;
        if (array_key_exists($id, $this->data[self::PROVIDERS])) {
            throw new InvalidArgumentException(sprintf(
                'ListenerProvider with ID "%s" has already been registered',
                $id
            ));
        }

        $this->data[self::PROVIDERS][$id] = $listenerProvider;
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

    public function getListenersForEvent(EventInterface $event): Generator
    {
        foreach ($this->data[self::LISTENERS] as $type => $priorities) {
            if ('object' !== $type && ! $event instanceof $type) {
                continue;
            }

            /** @var array<int, int> $priorities */
            foreach ($priorities as $priority) {
                /** @var array<int, Some<callable(EventInterface):void>> $priority */
                foreach ($priority as $listener) {
                    /** @var null|string $stop */
                    $stop = yield $listener->unwrap();
                    if (PHP_EOL === $stop) {
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

    /**
     * Resolves the class type of the first argument on a callable.
     *
     * @param callable(EventInterface<bool>):void $listener
     *
     * @throws FailedToDetermineTypeDeclarationsException if $listener is missing type-declarations
     * @throws FailedToDetermineTypeDeclarationsException if $listener class does not exist
     *
     * @return Generator<string>
     */
    private function getEventType(callable $listener, ?string $event = null): Generator
    {
        if (null !== $event) {
            yield $event;
            return;
        }

        try {
            $parameters = (new ReflectionFunction(Closure::fromCallable($listener)))->getParameters();
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

        yield from array_map(static function (ReflectionParameter $reflectionParameter): mixed {
            /** @var null|ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType $reflectionType */
            $reflectionType = $reflectionParameter->getType();

            if (! $reflectionType instanceof ReflectionType) {
                throw FailedToDetermineTypeDeclarationsException::missingTypeDeclarations(
                    $reflectionParameter->getName()
                );
            }

            if ($reflectionType instanceof ReflectionNamedType) {
                return $reflectionType->getName();
            }

            /** @var array<ReflectionNamedType> $reflectionTypeTypes */
            $reflectionTypeTypes = $reflectionType->getTypes();
            return array_map(
                static fn (
                    ReflectionNamedType $reflectionNamedType
                ): string => $reflectionNamedType->getName(),
                $reflectionTypeTypes
            );
        }, $parameters);
    }

    /**
     * Derives a unique ID from the listener.
     *
     * @param callable(EventInterface<bool>):void $listener
     */
    private function getListenerId(callable $listener): string
    {
        /** @var array{0:object|string,1:string}|object|string $listener */
        return match (true) {
            // Function callables are strings, so use that directly.
            is_string($listener) => $listener,
            /** @var object $listener */
            is_object($listener) => sprintf('listener.%s', spl_object_hash($listener)),
            // Object callable represents a method on an object.
            is_object($listener[0]) => sprintf('%s::%s', $listener[0]::class, $listener[1]),
            // Class callable represents a static class method.
            default => sprintf('%s::%s', $listener[0], $listener[1])
        };
    }
}
