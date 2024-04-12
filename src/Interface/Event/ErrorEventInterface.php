<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Throwable;

/**
 * An object that contains information about an error triggered by Event handling.
 */
interface ErrorEventInterface extends EventInterface
{
    /**
     * Returns the event that triggered this error event.
     *
     * @template TEvent of object
     *
     * @return TEvent
     */
    public function getEvent(): object;

    /**
     * Returns the listener that raised the error.
     *
     * @template TEvent of object
     * @template TListener of object
     *
     * @return class-string<(callable(TEvent):void)&TListener>
     */
    public function getListener(): string;

    /**
     * Returns the exception thrown by the listener.
     */
    public function getThrowable(): Throwable;
}
