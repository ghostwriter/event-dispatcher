<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\ListenerInterface;
use Ghostwriter\EventDispatcher\AbstractEvent;
use Throwable;

/**
 * @template TStopped of bool
 *
 * @extends AbstractEvent<TStopped>
 * @implements ErrorInterface<TStopped>
 */
final class ErrorEvent extends AbstractEvent implements ErrorInterface
{
    /**
     * @param EventInterface<bool> $event
     */
    public function __construct(
        private readonly EventInterface $event,
        private readonly ListenerInterface       $listener,
        private readonly Throwable      $throwable
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

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
