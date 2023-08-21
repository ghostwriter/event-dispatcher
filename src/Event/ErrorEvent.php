<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\Event;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\Traits\EventTrait;

/**
 * @template TPropagationStopped of bool
 *
 * @implements Error<TPropagationStopped>
 */
final class ErrorEvent implements Error
{
    use EventTrait;

    /**
     * @param Event<bool> $event
     */
    public function __construct(
        private readonly Event $event,
        private readonly Listener $listener,
        private readonly \Throwable $throwable
    ) {
    }

    /**
     * @return Event<bool>
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getListener(): Listener
    {
        return $this->listener;
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
