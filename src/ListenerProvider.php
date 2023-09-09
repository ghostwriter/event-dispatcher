<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Closure;
use Fiber;
use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\ContainerInterface;
use Ghostwriter\Container\Exception\NotFoundExceptionInterface;
use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\ListenerInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

use function array_key_exists;
use function array_map;
use function class_exists;
use function interface_exists;
use function is_a;
use function is_object;
use function is_string;
use function is_subclass_of;
use function krsort;
use function spl_object_hash;
use function sprintf;

use const SORT_NUMERIC;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * Map of registered Listeners, Providers and Subscribers.
     *
     * @var non-empty-array<class-string<EventInterface<bool>>,array<int,array<string,ListenerInterface>>>
     */
    private array $listeners = [];

    public function __construct(
        private ?ContainerInterface $container = null
    ) {
    }

    /**
     * @throws ListenerAlreadyExistsException
     */
    public function addListener(
        callable $listener,
        int $priority = 0,
        string $event = null,
        string $id = null
    ): string {
        $id ??= $this->getListenerId($listener);
        $events = $this->getEventType($listener, $event);
        foreach ($events as $event) {
            if (!class_exists($event) && !interface_exists($event)) {
                throw new EventNotFoundException($event);
            }

            if (
                array_key_exists($event, $this->listeners)
                && array_key_exists($priority, $this->listeners[$event])
                && array_key_exists($id, $this->listeners[$event][$priority])
            ) {
                throw new ListenerAlreadyExistsException($id);
            }

            /*
             * @var class-string<EventInterface<bool>> $event
             */
            $this->listeners[$event][$priority][$id] = new Listener($listener);

            krsort($this->listeners[$event], SORT_NUMERIC);
        }

        return $id;
    }

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws SubscriberMustImplementSubscriberInterfaceException
     * @throws NotFoundExceptionInterface
     */
    public function addSubscriber(string $subscriber): void
    {
        if (!is_subclass_of($subscriber, SubscriberInterface::class)) {
            throw new SubscriberMustImplementSubscriberInterfaceException($subscriber);
        }

        ($this->getContainer()->get($subscriber))($this);
    }

    /**
     * @param class-string<EventInterface<bool>> $event
     * @param callable-string           $listener
     *
     * @throws EventMustImplementEventInterfaceException
     */
    public function bindListener(string $event, string $listener, int $priority = 0, string $id = null): string
    {
        if (!is_a($event, EventInterface::class, true)) {
            throw new EventMustImplementEventInterfaceException();
        }

        return $this->addListener(
            fn (EventInterface $event): mixed => $this->getContainer()->call($listener, [$event]),
            $priority,
            $event,
            $id
        );
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container ??= Container::getInstance();
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @return Generator<ListenerInterface>
     */
    public function getListenersForEvent(EventInterface $event): Generator
    {
        $fiber = new Fiber(
            /**
             * @param EventInterface<bool> $event
             */
            function (EventInterface $event): void {
                // foreach ($this->observers as $observer) {
                //     Fiber::suspend($this->getContainer()->get($observer));
                // }

                foreach ($this->listeners as $type => $priorities) {
                    if (!$event instanceof $type) {
                        continue;
                    }

                    foreach ($priorities as $priority) {
                        foreach ($priority as $listener) {
                            if (!$listener instanceof ListenerInterface) {
                                continue;
                            }

                            Fiber::suspend($listener);
                        }
                    }
                }
            }
        );

        /**
         * @var ListenerInterface $listener
         */
        $listener = $fiber->start($event);
        if ($listener instanceof ListenerInterface) {
            yield $listener;
        }

        while ($fiber->isSuspended()) {
            /**
             * @var ListenerInterface $listener
             */
            $listener = $fiber->resume();
            if (!$listener instanceof ListenerInterface) {
                continue;
            }

            yield $listener;
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

        throw new ListenerNotFoundException($listenerId);
    }

    /**
     * Resolves the class type of the first argument on a callable.
     *
     * @param callable(EventInterface<bool>):void $listener
     *
     * @return Generator<class-string<EventInterface<bool>>|string>
     *
     * @throws MissingParameterTypeDeclarationException
     * @throws MissingEventParameterException
     * @throws FailedToDetermineEventTypeException
     */
    private function getEventType(callable $listener, string $event = null): Generator
    {
        if (null !== $event) {
            yield $event;

            return;
        }

        try {
            $parameters = (new ReflectionFunction(Closure::fromCallable($listener)))->getParameters();
        } catch (ReflectionException $reflectionException) {
            throw new FailedToDetermineEventTypeException($reflectionException->getMessage());
        }

        if ([] === $parameters) {
            throw new MissingEventParameterException('$event');
        }

        yield from array_map(
            static function (ReflectionParameter $reflectionParameter): mixed {
                /**
                 * @var ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null $reflectionType
                 */
                $reflectionType = $reflectionParameter->getType();

                if (!$reflectionType instanceof ReflectionType) {
                    throw new MissingParameterTypeDeclarationException($reflectionParameter->getName());
                }

                if ($reflectionType instanceof ReflectionNamedType) {
                    return $reflectionType->getName();
                }

                /**
                 * @var array<ReflectionNamedType> $reflectionTypeTypes
                 */
                $reflectionTypeTypes = $reflectionType->getTypes();

                return array_map(
                    static fn (
                        ReflectionNamedType $reflectionNamedType
                    ): string => $reflectionNamedType->getName(),
                    $reflectionTypeTypes
                );
            },
            $parameters
        );
    }

    /**
     * Derives a unique ID from the listener.
     *
     * @param callable(EventInterface<bool>):void $listener
     */
    private function getListenerId(callable $listener): string
    {
        /* @var array{0:object|string,1:string}|object|string $listener */
        return match (true) {
            // Function callables are strings, so use that directly.
            is_string($listener) => $listener,
            /* @var object $listener */
            is_object($listener) => sprintf('listener.%s', spl_object_hash($listener)),
            // Object callable represents a method on an object.
            is_object($listener[0]) => sprintf('%s::%s', $listener[0]::class, $listener[1]),
            // Class callable represents a static class method.
            default => sprintf('%s::%s', $listener[0], $listener[1])
        };
    }
}
