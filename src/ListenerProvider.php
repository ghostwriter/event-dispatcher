<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface as ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface as ContainerExceptionInterface;
use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\Exception\SubscriberNotFoundException;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Interface\SubscriberInterface;
use Throwable;

use function array_key_exists;
use function class_exists;
use function interface_exists;
use function is_a;
use function method_exists;
use function enum_exists;
use function trait_exists;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @template TEvent of object
     * @template TListener of object
     *
     * @param array<class-string<(callable(TEvent):void)&TListener>>             $listeners
     * @param array<class-string<SubscriberInterface>,ListenerProviderInterface> $subscribers
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private array $listeners = [],
        private array $subscribers = [],
    ) {}

    /**
     * @template TEvent of object
     * @template TListener of object
     *
     * @param array<class-string<TEvent>,class-string<(callable(TEvent):void)&TListener>> $listeners
     * @param array<class-string<SubscriberInterface>>                                    $subscribers
     *
     * @throws Throwable
     */
    public static function new(
        ?ContainerInterface $container = null,
        array $listeners = [],
        array $subscribers = [],
    ): self {
        $container ??= Container::getInstance();

        $listenerProvider = new self($container);

        foreach ($listeners as $event => $listener) {
            $listenerProvider->listen($event, $listener);
        }

        foreach ($subscribers as $subscriber) {
            $listenerProvider->subscribe($subscriber);
        }

        return $listenerProvider;
    }

    /**
     * @template TEvent of object
     * @template TListener of object
     *
     * @param class-string<TEvent>                            $event
     * @param class-string<(callable(TEvent):void)&TListener> $listener
     *
     * @throws ExceptionInterface
     */
    public function listen(string $event, string $listener): void
    {
        self::assertEvent($event);

        self::assertListener($listener);

        if (
            array_key_exists($event, $this->listeners)
            && array_key_exists($listener, $this->listeners[$event])
        ) {
            throw new ListenerAlreadyExistsException($listener);
        }

        $this->listeners[$event][$listener] = $listener;
    }

    /**
     * @template TEvent of object
     * @template TListener of object
     *
     * @param TEvent $event
     *
     * @return Generator<class-string<(callable(TEvent):void)&TListener>>
     */
    public function getListenersForEvent(object $event): Generator
    {
        if (
            $event instanceof EventInterface
            && $event->isPropagationStopped()
        ) {
            return;
        }

        foreach ($this->listeners as $type => $listeners) {
            if (! $event instanceof $type) {
                continue;
            }

            yield from $listeners;
        }

        foreach ($this->subscribers as $provider) {
            if (! $provider instanceof ListenerProviderInterface) {
                continue;
            }

            yield from $provider->getListenersForEvent($event);
        }
    }

    /**
     * @template TEvent of object
     * @template TListener of object
     *
     * @param class-string<(callable(TEvent):void)&TListener> $listener
     *
     * @throws ListenerNotFoundException
     */
    public function forget(string $listener): void
    {
        $removed = false;

        foreach ($this->listeners as $event => $listeners) {
            if (! array_key_exists($listener, $listeners)) {
                continue;
            }

            unset($this->listeners[$event][$listener]);

            $removed = true;
        }

        if (! $removed) {
            throw new ListenerNotFoundException($listener);
        }

        $this->container->remove($listener);
    }

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws SubscriberMustImplementSubscriberInterfaceException
     * @throws ContainerNotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function subscribe(string $subscriber): void
    {
        if (! is_a($subscriber, SubscriberInterface::class, true)) {
            throw new SubscriberMustImplementSubscriberInterfaceException($subscriber);
        }

        if (array_key_exists($subscriber, $this->subscribers)) {
            throw new SubscriberAlreadyRegisteredException($subscriber);
        }

        $provider = self::new();

        $this->container->invoke($subscriber, [
            'provider' => $provider,
        ]);

        $this->subscribers[$subscriber] = $provider;
    }

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws SubscriberNotFoundException
     * @throws Throwable
     */
    public function unsubscribe(string $subscriber): void
    {
        if (! array_key_exists($subscriber, $this->subscribers)) {
            throw new SubscriberNotFoundException($subscriber);
        }

        unset($this->subscribers[$subscriber]);

        $this->container->remove($subscriber);
    }

    /**
     * @template TEvent of object
     *
     * @param class-string<TEvent>|string $event
     *
     * @psalm-assert class-string<TEvent> $event
     *
     * @throws EventMustImplementEventInterfaceException
     */
    private static function assertEvent(string $event): void
    {
        if (
            ! class_exists($event) &&
            ! interface_exists($event) &&
                        ! trait_exists($event) &&
                        ! enum_exists($event)
        ) {
            throw new EventNotFoundException($event);
        }

        if (! is_a($event, EventInterface::class, true)) {
            throw new EventMustImplementEventInterfaceException($event);
        }
    }

    /**
     * @template TEvent of object
     * @template TListener of object
     *
     * @param class-string<(callable(TEvent):void)&TListener>|string $listener
     *
     * @psalm-assert class-string<(callable(TEvent):void)&TListener> $listener
     *
     * @throws ListenerNotFoundException
     * @throws ListenerMissingInvokeMethodException
     */
    private static function assertListener(string $listener): void
    {
        if (! class_exists($listener)) {
            throw new ListenerNotFoundException($listener);
        }

        if (! method_exists($listener, '__invoke')) {
            throw new ListenerMissingInvokeMethodException($listener);
        }
    }
}
