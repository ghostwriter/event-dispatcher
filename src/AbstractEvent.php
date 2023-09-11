<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\EventInterface;

/**
 * @template TStopped of bool
 *
 * @implements EventInterface<TStopped>
 */
abstract class AbstractEvent implements EventInterface
{
    /**
     * @var TStopped
     */
    private bool $stopped = false;

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * @psalm-this-out self<true>
     */
    public function stop(): void
    {
        $this->stopped = true;
    }
}
