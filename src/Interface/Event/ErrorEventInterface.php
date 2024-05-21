<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

use Throwable;

/**
 * An object that contains information about an error triggered by Event handling.
 *
 * @template TEvent of object
 * @template TListener of object
 */
interface ErrorEventInterface
{
    /**
     * Returns the event that triggered this error event.
     *
     * @return TEvent
     */
    public function getEvent(): object;

    /**
     * Returns the listener that raised the error.
     *
     * @return class-string<(callable(TEvent):void)&TListener>
     */
    public function getListener(): string;

    /**
     * Returns the exception thrown by the listener.
     */
    public function getThrowable(): Throwable;
}
