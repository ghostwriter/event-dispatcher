<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

/**
 * An EventInterface that can stop propagation to any further Listeners.
 *
 * MUST be implemented to provide type-safety to both listeners and listener providers.
 *
 * @template TStopped of bool
 */
interface EventInterface
{
    /**
     * Determine if the previous listener halted propagation.
     *
     * @return (TStopped is true ? true : false)
     */
    public function isStopped(): bool;

    /**
     * Stop event propagation.
     *
     * @psalm-this-out self<true>
     */
    public function stop(): void;
}
