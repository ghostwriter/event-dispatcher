<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Interface;

/**
 * Subscribe Listeners to Events.
 */
interface SubscriberInterface
{
    /** Registers listeners on the given ListenerProviderInterface. */
    public function __invoke(ListenerProviderInterface $listenerProvider): void;
}
