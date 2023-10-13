<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerInterface;
use Throwable;

/**
 * An object that contains information about an error triggered by EventInterface handling.
 *
 * @template TStopped of bool
 *
 * @extends EventInterface<TStopped>
 */
interface ErrorEventInterface extends EventInterface
{
    /**
     * Returns the event that triggered this error condition.
     */
    public function getEvent(): EventInterface;

    /**
     * Returns the callable from which the exception or error was generated.
     */
    public function getListener(): ListenerInterface;

    /**
     * Returns the throwable (ExceptionInterface or ErrorInterface) that triggered this error condition.
     */
    public function getThrowable(): Throwable;
}
