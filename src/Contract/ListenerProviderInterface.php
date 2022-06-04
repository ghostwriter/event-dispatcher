<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Traversable;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
interface ListenerProviderInterface
{
    /**
     * @param callable(EventInterface):void $listener
     */
    public function addListener(
        callable $listener,
        int $priority = 0,
        ?string $event = null,
        ?string $id = null
    ): string;

    /**
     * @param callable(EventInterface):void $listener
     */
    public function addListenerAfter(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string;

    /**
     * @param callable(EventInterface):void $listener
     */
    public function addListenerBefore(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string;

    public function addListenerService(
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string;

    public function addListenerServiceAfter(
        string $listenerId,
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string;

    public function addListenerServiceBefore(
        string $listenerId,
        string $event,
        string $listener,
        int $priority = 0,
        ?string $id = null
    ): string;

    public function addProvider(self $psrListenerProvider): void;

    public function addSubscriber(SubscriberInterface $subscriber): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ContainerExceptionInterface
     */
    public function addSubscriberService(string $subscriber): void;

    /**
     * Return relevant/type-compatible Listeners for the Event.
     *
     * @return Traversable<callable(EventInterface):void>
     */
    public function getListenersForEvent(EventInterface $event): Traversable;

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
