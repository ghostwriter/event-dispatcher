<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Throwable;
use Override;

/**
 * @template Event of object
 * @template Listener of object
 *
 * @implements ErrorEventInterface<Event,Listener>
 */
final readonly class ErrorEvent implements ErrorEventInterface
{
    /**
     * @param Event                                         $event
     * @param class-string<(callable(Event):void)&Listener> $listener
     */
    public function __construct(
        private object $event,
        private string $listener,
        private Throwable $throwable
    ) {}

    /**
     * @return Event
     */
    #[Override]
    public function event(): object
    {
        return $this->event;
    }

    /**
     * @return class-string<(callable(Event):void)&Listener>
     */
    #[Override]
    public function listener(): string
    {
        return $this->listener;
    }

    #[Override]
    public function throwable(): Throwable
    {
        return $this->throwable;
    }
}
