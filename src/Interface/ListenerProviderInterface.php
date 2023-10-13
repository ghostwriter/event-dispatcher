<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Generator;

interface ListenerProviderInterface
{
    /**
     * @template TBind of class-string|callable-string
     *
     * @param class-string<EventInterface<bool>> $event
     * @param TBind $listener
     *
     * @return TBind
     *
     * @throws ExceptionInterface
     */
    public function bind(string $event, string $listener, int $priority = 0): string;

    /**
     * @template TListener of class-string|callable-string
     *
     * @param TListener $listener

     * @return TListener
     *
     * @throws ExceptionInterface
     */
    public function listen(string $listener, int $priority = 0): string;

    /**
     * @param EventInterface<bool> $event
     *
     * @return Generator<ListenerInterface>
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
