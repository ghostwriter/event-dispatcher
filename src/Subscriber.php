<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

/**
 * Subscribe Listeners to Events.
 */
interface Subscriber
{
    /**
     * Registers listeners on the given ListenerProvider.
     */
    public function __invoke(ListenerProvider $listenerProvider): void;
}
