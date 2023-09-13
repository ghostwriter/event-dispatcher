<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\ContainerInterface;
use Ghostwriter\Container\Exception\NotFoundExceptionInterface as ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\ExceptionInterface as ContainerExceptionInterface;
use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class Provider implements ProviderInterface
{
    private readonly ContainerInterface $container;

    /**
     * Map of registered Listeners.
     *
     * @var array<class-string<EventInterface<bool>>,array<int,array<string,ListenerInterface>>>
     */
    private array $listeners = [];

    /**
     * Map of registered Subscribers.
     *
     * @var array<class-string<SubscriberInterface>,bool>
     */
    private array $subscribers = [];

    public function __construct(
        ContainerInterface $container = null
    ) {
        $this->container = $container ?? Container::getInstance();
    }

    /**
     * @param class-string<EventInterface<bool>> $event
     * @param class-string|callable-string       $listener
     *
     * @throws EventMustImplementEventInterfaceException
     * @throws EventNotFoundException
     * @throws FailedToDetermineEventTypeException
     * @throws ListenerAlreadyExistsException
     * @throws ListenerMissingInvokeMethodException
     * @throws ListenerNotFoundException
     * @throws MissingEventParameterException
     * @throws MissingParameterTypeDeclarationException
     */
    public function bind(
        string $event,
        string $listener,
        int $priority = 0,
        string $id = null
    ): string {
        if (!is_a($event, EventInterface::class, true)) {
            throw new EventMustImplementEventInterfaceException();
        }

        if (!class_exists($listener)) {
            throw new ListenerNotFoundException($listener);
        }

        if (!method_exists($listener, '__invoke')) {
            throw new ListenerMissingInvokeMethodException($listener);
        }

        $id ??= $listener;

        if (
            \array_key_exists($event, $this->listeners)
            && \array_key_exists($priority, $this->listeners[$event])
            && \array_key_exists($id, $this->listeners[$event][$priority])
        ) {
            throw new ListenerAlreadyExistsException($id);
        }

        $this->listeners[$event][$priority][$id] = Listener::fromInvokableClass($listener);

        krsort($this->listeners[$event], \SORT_NUMERIC);

        return $id;
    }

    /**
     * @param callable(EventInterface<bool>):void $listener
     *
     * @throws EventNotFoundException
     * @throws FailedToDetermineEventTypeException
     * @throws ListenerAlreadyExistsException
     * @throws MissingEventParameterException
     * @throws MissingParameterTypeDeclarationException
     */
    public function listen(
        callable $listener,
        int $priority = 0,
        string $event = null,
        string $id = null
    ): string {
        [$listenerId, $events] = $this->resolve($listener, $event, $id);

        foreach ($events as $event) {
            if (!class_exists($event) && !interface_exists($event)) {
                throw new EventNotFoundException($event);
            }

            if (
                \array_key_exists($event, $this->listeners)
                && \array_key_exists($priority, $this->listeners[$event])
                && \array_key_exists($listenerId, $this->listeners[$event][$priority])
            ) {
                throw new ListenerAlreadyExistsException($listenerId);
            }

            /*
             * @var class-string<EventInterface<bool>> $event
             */
            $this->listeners[$event][$priority][$listenerId] = new Listener($listener(...));

            krsort($this->listeners[$event], \SORT_NUMERIC);
        }

        return $listenerId;
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @return \Generator<int,ListenerInterface,mixed,void>
     */
    public function listeners(EventInterface $event): \Generator
    {
        foreach ($this->listeners as $type => $priorities) {
            if (!$event instanceof $type) {
                continue;
            }

            foreach ($priorities as $priority) {
                foreach ($priority as $listener) {
                    if (!$listener instanceof ListenerInterface) {
                        continue;
                    }

                    yield $listener;
                }
            }
        }
    }

    /**
     * @throws ListenerNotFoundException
     */
    public function remove(string $listenerId): void
    {
        foreach ($this->listeners as $event => $listeners) {
            foreach ($listeners as $priority => $listener) {
                if (\array_key_exists($listenerId, $listener)) {
                    unset($this->listeners[$event][$priority][$listenerId]);

                    return;
                }
            }
        }

        throw new ListenerNotFoundException($listenerId);
    }

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws \Throwable
     * @throws SubscriberMustImplementSubscriberInterfaceException
     * @throws ContainerNotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     */
    public function subscribe(string $subscriber): void
    {
        if (!is_a($subscriber, SubscriberInterface::class, true)) {
            throw new SubscriberMustImplementSubscriberInterfaceException($subscriber);
        }

        if (\array_key_exists($subscriber, $this->subscribers)) {
            throw new SubscriberAlreadyRegisteredException($subscriber);
        }

        $this->subscribers[$subscriber] = true;

        $this->container->call($subscriber, [$this]);
    }

    /** @return array<class-string<EventInterface<bool>>> */
    private function eventFromReflectionIntersectionOrUnionType(
        \ReflectionIntersectionType|\ReflectionUnionType $reflectionIntersectionOrUnionType
    ): array {
        $events = [];

        foreach ($reflectionIntersectionOrUnionType->getTypes() as $reflectionType) {
            if ($reflectionType instanceof \ReflectionNamedType) {
                /** @var class-string<EventInterface<bool>> $reflectionTypeName */
                $reflectionTypeName = $reflectionType->getName();

                $events[] = $reflectionTypeName;

                continue;
            }

            $events = array_merge($events, $this->eventFromReflectionIntersectionOrUnionType($reflectionType));
        }

        /** @var array<class-string<EventInterface<bool>>> */
        return $events;
    }

    /**
     * @param callable(EventInterface<bool>):void $listener
     *
     * @return list{non-falsy-string,list{class-string<EventInterface<bool>>}} [$listenerId, $events]
     *
     * @throws FailedToDetermineEventTypeException
     * @throws MissingEventParameterException
     * @throws MissingParameterTypeDeclarationException
     */
    private function resolve(
        callable $listener,
        string $event = null,
        string $id = null
    ): array {
        /**
         * Closure(EventInterface<bool>):void.
         */
        $closure = $listener(...);

        try {
            $reflectionFunction = new \ReflectionFunction($closure);
        } catch (\ReflectionException $reflectionException) {
            throw new FailedToDetermineEventTypeException($reflectionException->getMessage());
        }

        $id ??= sprintf('%s:%s', $reflectionFunction->getFileName(), $reflectionFunction->getStartLine());

        $listenerId = 'listener.' . md5($id);

        if (null !== $event) {
            /**
             * @var list{non-falsy-string,list{class-string<EventInterface<bool>>}}
             */
            return [$listenerId, [$event]];
        }

        $parameters = $reflectionFunction->getParameters();

        if ([] === $parameters) {
            throw new MissingEventParameterException('$event');
        }

        $events = [];

        foreach ($parameters as $parameter) {
            $reflectionType = $parameter->getType();

            if ($reflectionType instanceof \ReflectionNamedType) {
                $events[] = $reflectionType->getName();

                continue;
            }

            if (
                !$reflectionType instanceof \ReflectionUnionType
                && !$reflectionType instanceof \ReflectionIntersectionType
            ) {
                throw new MissingParameterTypeDeclarationException($parameter->getName());
            }

            $events = array_merge(
                $events,
                $this->eventFromReflectionIntersectionOrUnionType($reflectionType)
            );
        }

        /**
         * @var list{non-falsy-string,list{class-string<EventInterface<bool>>}}
         */
        return [$listenerId, $events];
    }
}
