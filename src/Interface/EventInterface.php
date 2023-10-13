<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

/**
 * @template TDone of bool
 */
interface EventInterface
{
    /**
     * @return (TDone is true ? true : false)
     */
    public function isDone(): bool;

    /**
     * @psalm-this-out self<true>
     */
    public function done(): void;
}
