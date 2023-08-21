<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\ContainerInterface;
use Ghostwriter\Container\Exception\NotFoundExceptionInterface;
use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use Ghostwriter\EventDispatcher\Exception\ListererAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\Traits\ListenerTrait;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class EventListenerProvider implements ListenerProvider
{
    /**
     * Map of registered Listeners, Providers and Subscribers.
     *
     * @var array<class-string<Event<bool>>,array<int,array<string,Listener>>>
     */
    private array $listeners = [];

    public function __construct(
        private ?ContainerInterface $container = null
    ) {
    }

    /**
     * @throws ListererAlreadyExistsException
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

            if (\array_key_exists($event, $this->listeners)
                && \array_key_exists($priority, $this->listeners[$event])
                && \array_key_exists($id, $this->listeners[$event][$priority])
            ) {
                throw new ListererAlreadyExistsException($id);
            }

            /*
             * @var class-string<Event<bool>> $event
             */
            $this->listeners[$event][$priority][$id]
                = new class($listener) implements Listener {
                    use ListenerTrait;
                };

            krsort($this->listeners[$event], \SORT_NUMERIC);
        }

        return $id;
    }

    /**
     * @param class-string<Subscriber> $subscriber
     *
     * @throws SubscriberMustImplementSubscriberInterfaceException
     * @throws NotFoundExceptionInterface
     */
    public function addSubscriber(string $subscriber): void
    {
        if (!is_subclass_of($subscriber, Subscriber::class)) {
            throw new SubscriberMustImplementSubscriberInterfaceException($subscriber);
        }

        ($this->getContainer()->get($subscriber))($this);
    }

    /**
     * @param class-string<Event<bool>> $event
     * @param callable-string           $listener
     *
     * @throws EventMustImplementEventInterfaceException
     */
    public function bindListener(string $event, string $listener, int $priority = 0, string $id = null): string
    {
        if (!is_a($event, Event::class, true)) {
            throw new EventMustImplementEventInterfaceException();
        }

        return $this->addListener(
            fn (Event $event): mixed => $this->getContainer()->call($listener, [$event]),
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
     * @param Event<bool> $event
     *
     * @return \Generator<Listener>
     */
    public function getListenersForEvent(Event $event): \Generator
    {
        $fiber = new \Fiber(
            /**
             * @param Event<bool> $event
             */
            function (Event $event): void {
                foreach ($this->listeners as $type => $priorities) {
                    if (!$event instanceof $type) {
                        continue;
                    }
                    foreach ($priorities as $priority) {
                        foreach ($priority as $listener) {
                            if (!$listener instanceof Listener) {
                                return;
                            }

                            \Fiber::suspend($listener);
                        }
                    }
                }
            }
        );

        $started = false;

        do {
            $started = $started || $fiber->isStarted();
            $listener = $started ? $fiber->resume() : $fiber->start($event);
            if (!$listener instanceof Listener) {
                continue;
            }

            yield $listener;
        } while ($fiber->isSuspended());
    }

    public function removeListener(string $listenerId): void
    {
        foreach ($this->listeners as $event => $listeners) {
            foreach ($listeners as $priority => $listener) {
                if (\array_key_exists($listenerId, $listener)) {
                    unset($this->listeners[$event][$priority][$listenerId]);

                    return;
                }
            }
        }

        throw new \InvalidArgumentException(sprintf('Listener "%s" cannot be found.', $listenerId));
    }

    /**
     * Resolves the class type of the first argument on a callable.
     *
     * @param callable(Event<bool>):void $listener
     *
     * @return \Generator<class-string<Event<bool>>|string>
     *
     * @throws MissingParameterTypeDeclarationException
     * @throws MissingEventParameterException
     * @throws FailedToDetermineEventTypeException
     */
    private function getEventType(callable $listener, string $event = null): \Generator
    {
        if (null !== $event) {
            yield $event;

            return;
        }

        try {
            $parameters = (new \ReflectionFunction(\Closure::fromCallable($listener)))->getParameters();
        } catch (\ReflectionException $reflectionException) {
            throw new FailedToDetermineEventTypeException($reflectionException->getMessage());
        }

        if ([] === $parameters) {
            throw new MissingEventParameterException('$event');
        }

        yield from array_map(
            static function (\ReflectionParameter $reflectionParameter): mixed {
                /**
                 * @var \ReflectionIntersectionType|\ReflectionNamedType|\ReflectionUnionType|null $reflectionType
                 */
                $reflectionType = $reflectionParameter->getType();

                if (!$reflectionType instanceof \ReflectionType) {
                    throw new MissingParameterTypeDeclarationException($reflectionParameter->getName());
                }

                if ($reflectionType instanceof \ReflectionNamedType) {
                    return $reflectionType->getName();
                }

                /**
                 * @var array<\ReflectionNamedType> $reflectionTypeTypes
                 */
                $reflectionTypeTypes = $reflectionType->getTypes();

                return array_map(
                    static fn (
                        \ReflectionNamedType $reflectionNamedType
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
     * @param callable(Event<bool>):void $listener
     */
    private function getListenerId(callable $listener): string
    {
        /* @var array{0:object|string,1:string}|object|string $listener */
        return match (true) {
            // Function callables are strings, so use that directly.
            \is_string($listener) => $listener,
            /* @var object $listener */
            \is_object($listener) => sprintf('listener.%s', spl_object_hash($listener)),
            // Object callable represents a method on an object.
            \is_object($listener[0]) => sprintf('%s::%s', $listener[0]::class, $listener[1]),
            // Class callable represents a static class method.
            default => sprintf('%s::%s', $listener[0], $listener[1])
        };
    }
}
