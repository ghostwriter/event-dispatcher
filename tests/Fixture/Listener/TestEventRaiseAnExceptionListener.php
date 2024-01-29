<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Listener;

use Ghostwriter\EventDispatcherTests\Fixture\TestEvent;

final class TestEventRaiseAnExceptionListener
{
    public function __invoke(TestEvent $event): void
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
