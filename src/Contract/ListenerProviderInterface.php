<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Generator;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
interface ListenerProviderInterface
{
    /**
     * @param callable(EventInterface):void            $listener
     * @param null|class-string<EventInterface>|string $event
     */
    public function addListener(
        callable $listener,
        int $priority = 0,
        ?string $event = null,
        ?string $id = null
    ): string;

    /**
     * @param callable(EventInterface):void            $listener
     * @param null|class-string<EventInterface>|string $event
     */
    public function addListenerAfter(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string;

    /**
     * @param callable(EventInterface):void            $listener
     * @param null|class-string<EventInterface>|string $event
     */
    public function addListenerBefore(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string;

    /**
     * @param class-string<EventInterface>|string $event
     */
    public function addListenerService(
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string;

    /**
     * @param class-string<EventInterface>|string $event
     */
    public function addListenerServiceAfter(
        string $listenerId,
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string;

    /**
     * @param class-string<EventInterface>|string $event
     */
    public function addListenerServiceBefore(
        string $listenerId,
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string;

    public function addProvider(self $listenerProvider): void;

    public function addSubscriber(SubscriberInterface $subscriber): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ContainerExceptionInterface
     */
    public function addSubscriberService(string $subscriber): void;

    /**
     * @return Generator<callable(EventInterface):void> an iterable of callables type-compatible with $event
     */
    public function getListenersForEvent(EventInterface $event): Generator;

    public function removeListener(string $listenerId): void;

    /**
     * @param class-string<ListenerProviderInterface> $providerId
     */
    public function removeProvider(string $providerId): void;

    /**
     * @param class-string<SubscriberInterface> $subscriberId
     */
    public function removeSubscriber(string $subscriberId): void;
}
