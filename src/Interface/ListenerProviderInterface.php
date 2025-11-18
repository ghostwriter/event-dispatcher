<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;

interface ListenerProviderInterface extends PsrListenerProviderInterface
{
    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param 'object'|class-string<Event>                  $event
     * @param class-string<(callable(Event):void)&Listener> $listener
     *
     * @throws ExceptionInterface
     */
    public function bind(string $event, string $listener): void;

    /**
     * @template Event of object
     * @template Listener of (callable(Event):void)&object
     *
     * @param Event $event
     *
     * @return iterable<class-string<Listener>>
     */
    public function getListenersForEvent(object $event): iterable;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ExceptionInterface
     */
    public function subscribe(string $subscriber): void;

    /**
     * @template Event of object
     * @template Listener of (callable(Event):void)&object
     *
     * @param class-string<Listener> $listener
     *
     * @throws ExceptionInterface
     */
    public function unbind(string $listener): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ExceptionInterface
     */
    public function unsubscribe(string $subscriber): void;
}
