<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent2;

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
