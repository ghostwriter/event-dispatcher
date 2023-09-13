<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

/**
 * Subscribe Listeners to Events.
 */
interface SubscriberInterface
{
    /**
     * Registers listeners on the given ProviderInterface.
     */
    public function __invoke(ProviderInterface $provider): void;
}
