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
    /**
     * @var TStopPropagation
     */
    private bool $propagationStopped = false;

    /**
     * @return (TStopPropagation is true ? true : false)
     */
    final public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * @psalm-this-out self<true>
     */
    final public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
