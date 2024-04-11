<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEvent2;

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
