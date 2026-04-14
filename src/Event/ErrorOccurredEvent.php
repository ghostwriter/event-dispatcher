<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorOccurredEventInterface;
use Override;
use Throwable;

/**
 * @template TEvent of object
 * @template TListener of class-string<(callable(TEvent):void)&object>
 * @template TReason of Throwable
 *
 * @implements ErrorOccurredEventInterface<TEvent, TListener, TReason>
 */
final class ErrorOccurredEvent implements ErrorOccurredEventInterface
{
    private bool $propagationStopped = false;

    /**
     * @param TEvent    $event
     * @param TListener $listener
     * @param TReason   $throwable
     */
    public function __construct(
        private readonly object $event,
        private readonly string $listener,
        private readonly Throwable $throwable
    ) {}

    /** @return TEvent */
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

    /** @return TListener */
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

    /** @return TReason */
    #[Override]
    public function throwable(): Throwable
    {
        return $this->throwable;
    }
}
