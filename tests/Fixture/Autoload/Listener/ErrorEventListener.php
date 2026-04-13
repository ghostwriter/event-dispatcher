<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;

final class ErrorEventListener
{
    /**
     * @throws \RuntimeException
     */
    public function __invoke(ErrorOccurredEvent $event): never
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
