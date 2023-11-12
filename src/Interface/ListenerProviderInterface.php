<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Generator;

interface ListenerProviderInterface
{
    /**
     * @param class-string<EventInterface<bool>> $event
     * @param class-string|callable-string       $listener
     *
     * @throws ExceptionInterface
     */
    public function bind(string $event, string $listener, int $priority = 0): void;

    /**
     * @param class-string|callable-string $listener
     *
     * @throws ExceptionInterface
     */
    public function listen(string $listener, int $priority = 0): void;

    /**
     * @param EventInterface<bool> $event
     *
     * @return Generator<callable(EventInterface<bool>):void>
     */
    public function getListenersForEvent(EventInterface $event): Generator;

    /**
     * @param class-string|callable-string $listenerId
     *
     * @throws ExceptionInterface
     */
    public function remove(string $listenerId): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ExceptionInterface
     */
    public function subscribe(string $subscriber): void;
}
