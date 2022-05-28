<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\EventInterface;

/**
 * @template TPropagationStopped of bool
 * @implements EventInterface<TPropagationStopped>
 */
abstract class AbstractEvent implements EventInterface
{
    private bool $propagationStopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
