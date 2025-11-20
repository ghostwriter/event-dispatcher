<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;

interface ListenerProviderInterface extends PsrListenerProviderInterface
{
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
     * @template Event of object
     * @template Listener of object
     *
     * @param 'object'|class-string<Event>                  $event
     * @param class-string<(callable(Event):void)&Listener> $listener
     *
     * @throws ExceptionInterface
     */
    public function listen(string $event, string $listener): void;

    /**
     * @template Event of object
     * @template Listener of (callable(Event):void)&object
     *
     * @param class-string<Listener> $listener
     *
     * @throws ExceptionInterface
     */
    public function remove(string $listener): void;
}
