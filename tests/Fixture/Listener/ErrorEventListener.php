<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;

final class ErrorEventListener
{
    /** @param ErrorEvent<bool> $event */
    public function __invoke(ErrorEvent $event): void
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
