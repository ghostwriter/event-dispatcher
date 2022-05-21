<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Ghostwriter\Container\Contract\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;
use Traversable;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
interface ListenerProviderInterface extends PsrListenerProviderInterface
{
    public function addListener(
        callable $listener,
        int $priority = 0,
        ?string $event = null,
        ?string $id = null
    ): string;

    public function addListenerAfter(
        string $listenerId,
        callable $listener,
        ?string $event = null,
        ?string $id = null
    ): string;

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

    public function addProvider(PsrListenerProviderInterface $psrListenerProvider): void;

    public function addSubscriber(SubscriberInterface $subscriber): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ContainerExceptionInterface
     */
    public function addSubscriberService(string $subscriber): void;

    public function getContainer(): ContainerInterface;

    /** @return Traversable<callable> */
    public function getListenersForEvent(object $event): iterable;

    public function removeListener(string $listenerId): void;

    /**
     * @param class-string<PsrListenerProviderInterface> $providerId
     */
    public function removeProvider(string $providerId): void;

    /**
     * @param class-string<SubscriberInterface> $subscriberId
     */
    public function removeSubscriber(string $subscriberId): void;
}
