<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Generator;

interface ListenerProviderInterface
{
    /**
     * @param class-string<EventInterface<bool>>                         $event
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     *
     * @throws ExceptionInterface
     */
    public function listen(string $event, string $listener): void;

    /**
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     *
     * @throws ExceptionInterface
     */
    public function forget(string $listener): void;

    /**
     * @param EventInterface<bool> $event
     *
     * @return Generator<class-string<(callable(EventInterface<bool>):void)&object>>
     */
    public function provide(EventInterface $event): Generator;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ExceptionInterface
     */
    public function subscribe(string $subscriber): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ExceptionInterface
     */
    public function unsubscribe(string $subscriber): void;
}
