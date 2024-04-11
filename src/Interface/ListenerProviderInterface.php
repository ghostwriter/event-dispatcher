<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Generator;

interface ListenerProviderInterface
{
    /**
     * @template TEvent of object
     *
     * @param class-string<TEvent>                         $event
     * @param class-string<(callable(TEvent):void)&object> $listener
     *
     * @throws ExceptionInterface
     */
    public function listen(string $event, string $listener): void;

    /**
     * @template TEvent of object
     *
     * @param class-string<(callable(TEvent):void)&object> $listener
     *
     * @throws ExceptionInterface
     */
    public function forget(string $listener): void;

    /**
     * @template TEvent of object
     *
     * @param TEvent $event
     *
     * @return Generator<class-string<(callable(TEvent):void)&object>>
     */
    public function getListenersForEvent(object $event): Generator;

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
