<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Interface\EventInterface;

/**
 * @template TStopPropagation of bool
 *
 * @implements EventInterface<TStopPropagation>
 */
abstract class AbstractEvent implements EventInterface
{
    private bool $stopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * @psalm-this-out self<true>
     */
    public function stopPropagation(): void
    {
        $this->stopped = true;
    }
}
