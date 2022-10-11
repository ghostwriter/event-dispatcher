<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;

/**
 * Maps registered Listeners, Providers and Subscribers.
 *
 * @template TEvent of object
 */
interface ListenerProviderInterface extends PsrListenerProviderInterface
{
    /**
     * @param callable(TEvent):void $listener
     */
    public function addListener(
        callable $listener,
        int $priority = 0,
        ?string $event = null,
        ?string $id = null
    ): string;

    /**
     * @param callable(TEvent):void $listener
     */
    public function addListenerAfter(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string;

    /**
     * @param callable(TEvent):void $listener
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
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param TEvent $event an event for which to return the relevant listeners
     *
     * @return iterable<callable(TEvent):void> an iterable of callables type-compatible with $event
     */
    public function getListenersForEvent(object $event): iterable;

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
