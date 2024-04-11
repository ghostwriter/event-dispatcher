<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Tests\Fixture\TestEvent;

final class TestEventRaiseAnExceptionListener
{
    public function __invoke(TestEvent $event): void
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
