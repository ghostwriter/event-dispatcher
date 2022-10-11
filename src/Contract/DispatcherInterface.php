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
     * Provide all relevant listeners an event to process.
     *
     * @template TEvent of object
     *
     * @param TEvent $event the object to process
     *
     * @throws Throwable
     *
     * @return TEvent the Event that was passed, now modified by listeners
     */
    public function dispatch(object $event): object;
}
