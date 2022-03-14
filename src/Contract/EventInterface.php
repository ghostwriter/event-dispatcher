<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEventInterface;

/**
 * An Event that can stop propagation to any further Listeners.
 *
 * MUST be implemented to provide type-safety to both listeners and listener providers.
 */
interface EventInterface extends PsrStoppableEventInterface
{
    /**
     * Determine if the previous listener halted propagation.
     */
    public function isPropagationStopped(): bool;

    /**
     * Stop event propagation.
     */
    public function stopPropagation(): void;
}
