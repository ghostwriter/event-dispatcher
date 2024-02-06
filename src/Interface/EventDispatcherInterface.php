<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

interface EventDispatcherInterface
{
    /**
     * Provide all relevant listeners an event to process.
     *
     * @param EventInterface<bool> $event
     *
     * @throws ExceptionInterface
     *
     * @return EventInterface<bool>
     */
    public function dispatch(EventInterface $event): EventInterface;
}
