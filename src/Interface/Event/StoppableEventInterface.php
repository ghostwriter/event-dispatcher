<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface\Event;

use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEventInterface;

/**
 * An Event that may be interrupted when the event has been handled.
 */
interface StoppableEventInterface extends PsrStoppableEventInterface
{
    public function stopPropagation(): void;
}
