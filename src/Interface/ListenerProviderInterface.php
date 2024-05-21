<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Generator;

interface ListenerProviderInterface
{
    /**
     * @template TLEvent of object
     * @template TLListener of object
     *
     * @param 'object'|class-string<TLEvent>                    $event
     * @param class-string<(callable(TLEvent):void)&TLListener> $listener
     *
     * @throws ExceptionInterface
     */
    public function listen(string $event, string $listener): void;

    /**
     * @template TFEvent of object
     * @template TFListener of (callable(TFEvent):void)&object
     *
     * @param class-string<TFListener> $listener
     *
     * @throws ExceptionInterface
     */
    public function forget(string $listener): void;

    /**
     * @template TGEvent of object
     * @template TGListener of (callable(TGEvent):void)&object
     *
     * @param TGEvent $event
     *
     * @return Generator<class-string<TGListener>>
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
