<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\ListenerInterface;
use Ghostwriter\EventDispatcher\Traits\EventTrait;
use Throwable;

/**
 * @template TPropagationStopped of bool
 *
 * @implements ErrorInterface<TPropagationStopped>
 */
final class ErrorEvent implements ErrorInterface
{
    use EventTrait;

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
