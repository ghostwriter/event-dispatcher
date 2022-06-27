<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use DateTimeImmutable;

/**
 * An Event that can stop propagation to any further Listeners.
 *
 * MUST be implemented to provide type-safety to both listeners and listener providers.
 */
interface EventInterface
{
    /**
     * When the event propagation stopped or null if the event has not halted.
     */
    public function getDateTimePropagationStopped(): ?DateTimeImmutable;

    /**
     * Determine if the previous listener halted propagation.
     */
    public function isPropagationStopped(): bool;

    /**
     * Stop event propagation.
     */
    public function stopPropagation(): void;
}
