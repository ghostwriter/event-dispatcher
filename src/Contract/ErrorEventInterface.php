<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Throwable;

/**
 * An object that contains information about an error triggered by Event handling.
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
     * @return callable(EventInterface):void
     */
    public function getListener(): callable;

    /**
     * Returns the throwable (Exception or Error) that triggered this error condition.
     */
    public function getThrowable(): Throwable;
}
