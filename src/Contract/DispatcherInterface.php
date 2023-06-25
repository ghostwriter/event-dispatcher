<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Throwable;

/**
 * Delegates dispatching an event to one or more dispatchers.
 */
interface DispatcherInterface
{
    /**
     * Provide all relevant listeners an event to process.
     *
     * @throws Throwable
     */
    public function dispatch(EventInterface $event): EventInterface;

    public function getListenerProvider(): ListenerProviderInterface;
}
