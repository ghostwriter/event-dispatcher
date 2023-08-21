<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\ExceptionInterface;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;

/**
 * Maps registered Listeners.
 */
interface ListenerProvider
{
    /**
     * @param callable(Event<bool>):void            $listener
     * @param class-string<Event<bool>>|string|null $event
     */
    public function addListener(callable $listener, int $priority = 0, string $event = null, string $id = null): string;

    /**
     * @param class-string<Event<bool>> $event
     * @param callable-string           $listener
     *
     * @throws ExceptionInterface
     */
    public function bindListener(string $event, string $listener, int $priority = 0, string $id = null): string;

    /**
     * @param Event<bool> $event
     *
     * @return \Generator<Listener> an iterable of callables type-compatible with $event
     */
    public function getListenersForEvent(Event $event): \Generator;

    public function removeListener(string $listenerId): void;

    /**
     * @param class-string<Subscriber> $subscriber
     *
     * @throws SubscriberMustImplementSubscriberInterfaceException
     */
    public function addSubscriber(string $subscriber): void;
}
