<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Generator;

interface ListenerProviderInterface
{
    /**
     * @param class-string<EventInterface<bool>>                       $event
     * @param class-string<callable(EventInterface<bool>):void&object> $listener
     *
     * @throws EventDispatcherExceptionInterface
     */
    public function bind(string $event, string $listener, int $priority = 0): void;

    /**
     * @param EventInterface<bool> $event
     *
     * @return Generator<class-string<callable(EventInterface<bool>):void&object>>
     */
    public function getListenersForEvent(EventInterface $event): Generator;

    /**
     * @param class-string<callable(EventInterface<bool>):void&object> $listener
     *
     * @throws EventDispatcherExceptionInterface
     */
    public function listen(string $listener, int $priority = 0): void;

    /**
     * @param class-string<callable(EventInterface<bool>):void&object> $listener
     *
     * @throws EventDispatcherExceptionInterface
     */
    public function remove(string $listener): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws EventDispatcherExceptionInterface
     */
    public function subscribe(string $subscriber): void;
}
