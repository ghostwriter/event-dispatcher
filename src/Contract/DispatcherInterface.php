<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Throwable;

/**
 * Delegates dispatching an event to one or more dispatchers.
 */
interface DispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * Provides type-compatible Listeners, an event to process.
     *
     * @throws Throwable
     */
    public function dispatch(object $event): object;
}
