<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Throwable;

/**
 * Delegates dispatching an event to one or more dispatchers.
 */
interface DispatcherInterface
{
    /**
     * Provide all relevant listeners an event to process.
     *
     * @param EventInterface<bool> $event
     *
     * @return EventInterface<bool>
     *
     * @throws Throwable
     */
    public function dispatch(EventInterface $event): EventInterface;

    public function listenerProvider(): ProviderInterface;
}
