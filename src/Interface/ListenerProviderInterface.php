<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;

interface ListenerProviderInterface extends PsrListenerProviderInterface
{
    /**
     * @template TEvent of object
     * @template TListener of (callable(TEvent):void)&object
     *
     * @param TEvent $event
     *
     * @return iterable<class-string<TListener>>
     */
    public function getListenersForEvent(object $event): iterable;

    /**
     * @template TEvent of object
     * @template TListener of (callable(TEvent):void)&object
     *
     * @param 'object'|class-string<TEvent> $event
     * @param class-string<TListener>       $listener
     *
     * @throws ExceptionInterface
     */
    public function listen(string $event, string $listener): void;

    /**
     * @template TEvent of object
     * @template TListener of (callable(TEvent):void)&object
     *
     * @param class-string<TListener> $listener
     *
     * @throws ExceptionInterface
     */
    public function remove(string $listener): void;
}
