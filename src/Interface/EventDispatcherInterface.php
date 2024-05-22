<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

interface EventDispatcherInterface
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
