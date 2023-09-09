<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\ContainerInterface;
use Ghostwriter\Container\ExceptionInterface;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;

/**
 * Maps registered Listeners.
 */
interface ListenerProviderInterface
{
    /**
     * @param callable(EventInterface<bool>):void            $listener
     * @param class-string<EventInterface<bool>>|string|null $event
     */
    public function addListener(callable $listener, int $priority = 0, string $event = null, string $id = null): string;

    /**
     * @param class-string<EventInterface<bool>> $event
     * @param callable-string           $listener
     *
     * @throws ExceptionInterface
     */
    public function bindListener(string $event, string $listener, int $priority = 0, string $id = null): string;

    /**
     * @param EventInterface<bool> $event
     *
     * @return \Generator<ListenerInterface> an iterable of callables type-compatible with $event
     */
    public function getListenersForEvent(EventInterface $event): \Generator;

    public function removeListener(string $listenerId): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws SubscriberMustImplementSubscriberInterfaceException
     */
    public function addSubscriber(string $subscriber): void;

    public function getContainer(): ContainerInterface;
}
