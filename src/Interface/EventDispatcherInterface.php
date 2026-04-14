<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

interface EventDispatcherInterface extends PsrEventDispatcherInterface
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
