<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\ListenerInterface;

/**
 * @template TStopped of bool
 *
 * @extends AbstractEvent<TStopped>
 *
 * @implements ErrorEventInterface<TStopped>
 */
final class ErrorEvent extends AbstractEvent implements ErrorEventInterface
{
    /**
     * @param EventInterface<bool> $event
     */
    public function __construct(
        private readonly EventInterface $event,
        private readonly ListenerInterface $listener,
        private readonly \Throwable $throwable
    ) {
    }

    /**
     * @return EventInterface<bool>
     */
    public function getEvent(): EventInterface
    {
        return $this->event;
    }

    public function getListener(): ListenerInterface
    {
        return $this->listener;
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
