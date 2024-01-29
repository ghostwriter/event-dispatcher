<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Listener;

use Ghostwriter\EventDispatcherTests\Fixture\TestEvent;
use Ghostwriter\EventDispatcherTests\Fixture\TestEvent2;

final class MissingParameterTypeDeclarationListener
{
    /**
     * @param object $event
     */
    public function __invoke($event): void
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
