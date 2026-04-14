<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

use Throwable;

/**
 * An object that contains information about an error triggered by Event handling.
 *
 * @template TEvent of object
 * @template TListener of class-string<(callable(TEvent):void)&object>
 * @template TReason of Throwable
 */
interface ErrorOccurredEventInterface extends StoppableEventInterface
{
    /**
     * Returns the event that triggered this error event.
     *
     * @return TEvent
     */
    public function event(): object;

    /**
     * Returns the listener that raised the error.
     *
     * @return TListener
     */
    public function listener(): string;

    /**
     * Returns the exception thrown by the listener.
     *
     * @return TReason
     */
    public function throwable(): Throwable;
}
