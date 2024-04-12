<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;

final class ErrorEventListener
{
    /**
     * @throws \RuntimeException
     */
    public function __invoke(ErrorEvent $event): never
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
