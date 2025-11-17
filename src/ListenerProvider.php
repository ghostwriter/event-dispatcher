<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface as ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface as ContainerExceptionInterface;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\Exception\SubscriberNotFoundException;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Interface\SubscriberInterface;
use Override;
use Throwable;

use function array_key_exists;
use function array_keys;
use function class_exists;
use function enum_exists;
use function interface_exists;
use function is_a;
use function method_exists;
use function trait_exists;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param array<class-string<Event>,array<class-string<(callable(Event):void)&Listener>,null>> $listeners
     * @param array<class-string<SubscriberInterface>,ListenerProviderInterface>                   $listenerProviders
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private array $listeners = [],
        private array $listenerProviders = [],
    ) {}

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param array<class-string<Event>,class-string<(callable(Event):void)&Listener>> $listeners
     * @param list<class-string<SubscriberInterface>>                                  $subscribers
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
            $listenerProvider->bind($event, $listener);
        }

        foreach ($subscribers as $subscriber) {
            $listenerProvider->subscribe($subscriber);
        }

        return $listenerProvider;
    }

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param 'object'|class-string<Event>                  $event
     * @param class-string<(callable(Event):void)&Listener> $listener
     *
     * @throws ExceptionInterface
     */
    #[Override]
    public function bind(string $event, string $listener): void
    {
        $this->assertEvent($event);

        $this->assertListener($listener);

        if (
            array_key_exists($event, $this->listeners)
            && array_key_exists($listener, $this->listeners[$event])
        ) {
            throw new ListenerAlreadyExistsException($listener);
        }

        $this->listeners[$event][$listener] = null;
    }

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param Event $event
     *
     * @return Generator<class-string<(callable(Event):void)&Listener>>
     */
    #[Override]
    public function listeners(object $event): Generator
    {
        foreach ($this->listeners as $type => $listeners) {
            if (! $event instanceof $type) {
                continue;
            }

            foreach (array_keys($listeners) as $listener) {
                yield $listener;
            }
        }

        foreach ($this->listenerProviders as $listenerProvider) {
            if (! $listenerProvider instanceof ListenerProviderInterface) {
                continue;
            }

            yield from $listenerProvider->listeners($event);
        }
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
    #[Override]
    public function subscribe(string $subscriber): void
    {
        if (! is_a($subscriber, SubscriberInterface::class, true)) {
            throw new SubscriberMustImplementSubscriberInterfaceException($subscriber);
        }

        if (array_key_exists($subscriber, $this->listenerProviders)) {
            throw new SubscriberAlreadyRegisteredException($subscriber);
        }

        $this->container->call($subscriber, [$this->listenerProviders[$subscriber] ??= self::new($this->container)]);
    }

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param class-string<(callable(Event):void)&Listener> $listener
     *
     * @throws ListenerNotFoundException
     */
    #[Override]
    public function unbind(string $listener): void
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

        $this->container->unset($listener);
    }

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws SubscriberNotFoundException
     * @throws Throwable
     */
    #[Override]
    public function unsubscribe(string $subscriber): void
    {
        if (! array_key_exists($subscriber, $this->listenerProviders)) {
            throw new SubscriberNotFoundException($subscriber);
        }

        unset($this->listenerProviders[$subscriber]);

        $this->container->unset($subscriber);
    }

    /**
     * @template Event of object
     *
     * @param class-string<Event>|string $event
     *
     * @psalm-assert class-string<Event> $event
     *
     * @throws EventNotFoundException
     */
    private function assertEvent(string $event): void
    {
        match (true) {
            default => throw new EventNotFoundException($event),
            'object' === $event,
            class_exists($event),
            interface_exists($event),
            trait_exists($event),
            enum_exists($event) => null,
        };
    }

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param class-string<(callable(Event):void)&Listener> $listener
     *
     * @psalm-assert class-string<(callable(Event):void)&Listener> $listener
     *
     * @throws ListenerNotFoundException
     * @throws ListenerMissingInvokeMethodException
     */
    private function assertListener(string $listener): void
    {
        if (! class_exists($listener)) {
            throw new ListenerNotFoundException($listener);
        }

        if (! method_exists($listener, '__invoke')) {
            throw new ListenerMissingInvokeMethodException($listener);
        }
    }
}
