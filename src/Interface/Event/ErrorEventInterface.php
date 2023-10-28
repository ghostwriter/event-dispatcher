<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

use Throwable;
use Ghostwriter\EventDispatcher\Interface\EventInterface;

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
     * Returns the event that triggered this error condition.
     */
    public function getEvent(): EventInterface;

    /**
     * Returns the callable from which the exception or error was generated.
     *
     * @return callable(EventInterface<TStopPropagation>): void
     */
    public function getListener(): mixed;

    /**
     * Returns the throwable (ExceptionInterface or ErrorInterface) that triggered this error condition.
     */
    public function getThrowable(): Throwable;
}
