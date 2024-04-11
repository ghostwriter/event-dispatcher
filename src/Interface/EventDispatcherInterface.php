<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

interface EventDispatcherInterface
{
    /**
     * Provide all relevant listeners an event to process.
     *
     * @template TEvent of object
     *
     * @param TEvent $event
     *
     * @throws ExceptionInterface
     *
     * @return TEvent
     */
    public function dispatch(object $event): object;
}
