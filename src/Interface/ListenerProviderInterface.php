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
     * @template TListener of class-string|callable-string
     *
     * @param EventInterface<bool> $event
     *
     * @return Generator<TListener):void>
     */
    public function getListenersForEvent(EventInterface $event): Generator;

    /**
     * @template TRemove of class-string|callable-string|non-empty-string
     *
     * @param TRemove $listenerId
     */
    public function remove(string $listenerId): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ExceptionInterface
     */
    public function subscribe(string $subscriber): void;
}
