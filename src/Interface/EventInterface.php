<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

/**
 * @template TStopPropagation of bool
 */
interface EventInterface
{
    /**
     * @return (TStopPropagation is true ? true : false)
     */
    public function isPropagationStopped(): bool;

    /**
     * @psalm-this-out self<true>
     */
    public function stopPropagation(): void;
}
