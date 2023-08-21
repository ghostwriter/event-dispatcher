<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

/**
 * Delegates dispatching an event to one or more dispatchers.
 */
interface Dispatcher
{
    /**
     * Provide all relevant listeners an event to process.
     *
     * @param Event<bool> $event
     *
     * @return Event<bool>
     *
     * @throws \Throwable
     */
    public function dispatch(Event $event): Event;

    public function getListenerProvider(): ListenerProvider;
}
