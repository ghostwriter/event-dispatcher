<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Throwable;

/**
 * An object that contains information about an error triggered by EventInterface handling.
 *
 * @template TStopPropagation of bool
 *
 * @extends EventInterface<TStopPropagation>
 */
interface ErrorEventInterface extends EventInterface
{
    /**
     * Returns the event that triggered this error event.
     *
     * @return EventInterface<TStopPropagation>
     */
    public function getEvent(): EventInterface;

    /**
     * Returns the callable from which the exception or error was generated.
     *
     * @return class-string<callable(EventInterface<TStopPropagation>):void&object>
     */
    public function getListener(): string;

    /**
     * Returns the throwable that triggered this error event.
     */
    public function getThrowable(): Throwable;
}
