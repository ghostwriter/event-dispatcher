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
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;
use Throwable;
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
 */
final class ListenerProvider implements ListenerProviderInterface
{
    private ContainerInterface $container;

    /**
     * Map of registered Listeners.
     *
     * @var array<string,array<int,array<string,callable(EventInterface):void>>>
     */
    private array $listeners = [];

    /**
     * Map of registered Providers.
     *
     * @var array<class-string<ListenerProviderInterface>,ListenerProviderInterface>
     */
    private array $providers = [];

    /**
     * Map of registered Subscribers.
     *
     * @var array<class-string<SubscriberInterface>,SubscriberInterface>
     */
    private array $subscribers = [];

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container ?? Container::getInstance();
    }

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
            array_key_exists($event, $this->listeners) &&
            array_key_exists($priority, $this->listeners[$event]) &&
            array_key_exists($id, $this->listeners[$event][$priority])
        ) {
            throw new InvalidArgumentException(sprintf(
                'Duplicate Listener "%s" detected for "%s" Event.',
                $id,
                $event
            ));
        }

        $this->listeners[$event][$priority][$id] = $listener;

        krsort($this->listeners[$event], SORT_NUMERIC);

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

        foreach ($this->listeners[$event] as $listenerPriority => $listenerKey) {
            if (array_key_exists($id, $listenerKey)) {
                $priority = $listenerPriority;

                break;
            }
        }

        if (null === $priority) {
            throw new InvalidArgumentException(sprintf('Listener "%s" cannot be found.', $listenerId));
        }

        return $this->addListener($listener, $priority - 1, $event, $id);
    }

    public function addListenerBefore(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string {
        $event ??= $this->getEventType($listener);
        $id ??= $this->getListenerId($listener);
        $priority = null;

        foreach ($this->listeners[$event] as $listenerPriority => $listenerKey) {
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
            function (EventInterface $event) use ($listener): void {
                /** @var callable(EventInterface):void $callable */
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

        if (array_key_exists($id, $this->providers)) {
            throw new InvalidArgumentException(sprintf(
                'ListenerProvider with ID "%s" has already been registered',
                $id
            ));
        }

        $this->providers[$id] = $psrListenerProvider;
    }

    public function addSubscriber(SubscriberInterface $subscriber): void
    {
        if (array_key_exists($subscriber::class, $this->subscribers)) {
            throw new InvalidArgumentException(sprintf(
                'Subscriber with ID "%s" has already been registered',
                $subscriber::class
            ));
        }

        $subscriber($this);

        $this->subscribers[$subscriber::class] = $subscriber;
    }

    /** @throws Throwable */
    public function addSubscriberService(string $subscriber): void
    {
        if (! is_subclass_of($subscriber, SubscriberInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Subscriber with ID "%s" must implement %s.',
                $subscriber,
                SubscriberInterface::class
            ));
        }

        $this->addSubscriber($this->container->build($subscriber));
    }

    public function getListenersForEvent(EventInterface $event): Generator
    {
        foreach ($this->listeners as $type => $priorities) {
            if ('object' !== $type && ! $event instanceof $type) {
                continue;
            }

            foreach ($priorities as $priority) {
                foreach ($priority as $listener) {
                    yield $listener;
                }
            }
        }

        foreach ($this->providers as $provider) {
            yield from $provider->getListenersForEvent($event);
        }
    }

    public function removeListener(string $listenerId): void
    {
        foreach ($this->listeners as $event => $listeners) {
            foreach ($listeners as $priority => $listener) {
                if (array_key_exists($listenerId, $listener)) {
                    unset($this->listeners[$event][$priority][$listenerId]);
                    return;
                }
            }
        }

        throw new InvalidArgumentException(sprintf('Listener "%s" cannot be found.', $listenerId));
    }

    public function removeProvider(string $providerId): void
    {
        if (array_key_exists($providerId, $this->providers)) {
            unset($this->providers[$providerId]);

            return;
        }

        throw new InvalidArgumentException(sprintf('ListenerProvider "%s" cannot be found.', $providerId));
    }

    public function removeSubscriber(string $subscriberId): void
    {
        if (array_key_exists($subscriberId, $this->subscribers)) {
            unset($this->subscribers[$subscriberId]);

            return;
        }

        throw new InvalidArgumentException(sprintf('Subscriber "%s" cannot be found.', $subscriberId));
    }

    /**
     * Resolves the class type of the first argument on a callable.
     *
     * @param callable(EventInterface):void $listener
     *
     * @throws InvalidArgumentException if $listener does not define a valid Event argument with type declarations
     * @throws RuntimeException         if $listener does not exist
     */
    private function getEventType(callable $listener): string
    {
        try {
            $parameters = is_array($listener) ?
                (new ReflectionClass($listener[0]))->getMethod($listener[1])->getParameters() :
                (new ReflectionFunction(Closure::fromCallable($listener)))->getParameters();
        } catch (ReflectionException $reflectionException) {
            throw new RuntimeException(
                sprintf('Unable to determine type declarations; %s', $reflectionException->getMessage()),
                $reflectionException->getCode(),
                $reflectionException
            );
        }

        if ([] === $parameters) {
            throw new InvalidArgumentException('Missing first parameter "$event" and type declarations.');
        }

        $parameter = $parameters[0]->getType();

        if (! $parameter instanceof ReflectionType) {
            throw new InvalidArgumentException(
                sprintf('Missing type declarations for "$%s" parameter.', $parameters[0]->getName())
            );
        }

        if ($parameter instanceof ReflectionNamedType) {
            return $parameter->getName();
        }

        $reflectionType = $parameter instanceof ReflectionUnionType;
        throw new InvalidArgumentException(
            sprintf(
                'Invalid type declarations for "$%s" parameter; %s given.',
                $parameters[0]->getName(),
                $reflectionType ? 'UnionType' : 'IntersectionType'
            )
        );
    }

    /**
     * Derives a unique ID from the listener.
     *
     * @param callable(EventInterface):void $listener
     */
    private function getListenerId(callable $listener): string
    {
        if (is_string($listener)) {
            // Function callables are strings, so use that directly.
            return $listener;
        }

        if (is_object($listener)) {
            /** @var object $listener */
            return sprintf('listener.%s', md5(spl_object_hash($listener)));
        }

        /**
         * @var object|string $class
         * @var string        $method
         */
        [$class, $method] = $listener;

        if (is_object($class)) {
            // Object callable represents a method on an object.
            return sprintf('%s::%s', $class::class, $method);
        }

        // Class callable represents a static class method.
        return sprintf('%s::%s', $class, $method);
    }
}
