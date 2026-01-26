<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorOccurredEventInterface;
use Override;
use Throwable;

/**
 * @template Event of object
 * @template Listener of object
 * @template Reason of Throwable
 *
 * @implements ErrorOccurredEventInterface<Event, Listener, Reason>
 */
final class ErrorOccurredEvent implements ErrorOccurredEventInterface
{
    private bool $propagationStopped = false;

    /**
     * @param Event                                         $event
     * @param class-string<(callable(Event):void)&Listener> $listener
     * @param Reason                                        $throwable
     */
    public function __construct(
        private readonly object $event,
        private readonly string $listener,
        private readonly Throwable $throwable
    ) {}

    /** @return Event */
    #[Override]
    public function event(): object
    {
        return $this->event;
    }

    #[Override]
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /** @return class-string<(callable(Event):void)&Listener> */
    #[Override]
    public function listener(): string
    {
        return $this->listener;
    }

    #[Override]
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /** @return Reason */
    #[Override]
    public function throwable(): Throwable
    {
        return $this->throwable;
    }
}
