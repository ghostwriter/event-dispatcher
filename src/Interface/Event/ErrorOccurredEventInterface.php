<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

use Throwable;

/**
 * An object that contains information about an error triggered by Event handling.
 *
 * @template Event of object
 * @template Listener of object
 * @template Reason of Throwable
 */
interface ErrorOccurredEventInterface extends StoppableEventInterface
{
    /**
     * Returns the event that triggered this error event.
     *
     * @return Event
     */
    public function event(): object;

    /**
     * Returns the listener that raised the error.
     *
     * @return class-string<(callable(Event):void)&Listener>
     */
    public function listener(): string;

    /**
     * Returns the exception thrown by the listener.
     *
     * @return Reason
     */
    public function throwable(): Throwable;
}
