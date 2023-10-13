<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;

final class TestEventRaiseAnExceptionListener
{
    public function __invoke(TestEvent $event): void
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
