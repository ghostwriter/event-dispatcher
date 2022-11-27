<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

/**
 * An Event that can stop propagation to any further Listeners.
 *
 * MUST be implemented to provide type-safety to both listeners and listener providers.
 *
 * @template TPropagationStopped of bool
 */
interface EventInterface
{
    /**
     * Determine if the previous listener halted propagation.
     *
     * @return (TPropagationStopped is true ? true : false)
     */
    public function isPropagationStopped(): bool;

    /**
     * Stop event propagation.
     */
    public function stopPropagation(): void;
}
