<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

/**
 * Subscribe Listeners to Events.
 */
interface SubscriberInterface
{
    /**
     * Registers listeners on the given ListenerProvider.
     */
    public function __invoke(ListenerProviderInterface $listenerProvider): void;
}
