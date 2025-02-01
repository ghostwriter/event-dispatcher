<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

/**
 * An Event that may be interrupted when the event has been handled.
 */
interface StoppableEventInterface
{
    /**
     * Determine if the previous listener halted propagation.
     *
     * @return bool
     *              True if no further listeners should be called.
     *              False to continue calling listeners.
     */
    public function isPropagationStopped(): bool;
}
