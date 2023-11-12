<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Trait;

use Ghostwriter\EventDispatcher\Interface\EventInterface;

/**
 * @template TStopPropagation of bool
 *
 * @implements EventInterface<TStopPropagation>
 */
trait EventTrait
{
    private bool $stopped = false;

    final public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * @psalm-this-out self<true>
     */
    final public function stopPropagation(): void
    {
        $this->stopped = true;
    }
}
