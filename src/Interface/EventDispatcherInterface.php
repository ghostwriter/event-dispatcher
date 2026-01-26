<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * Provide all relevant listeners an event to process.
     *
     * @template Event of object
     *
     * @param Event $event
     *
     * @throws ExceptionInterface
     *
     * @return Event
     */
    public function dispatch(object $event): object;
}
