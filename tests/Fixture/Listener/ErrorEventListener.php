<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\ErrorEvent;

final class ErrorEventListener
{
    public function __invoke(ErrorEvent $event): void
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
