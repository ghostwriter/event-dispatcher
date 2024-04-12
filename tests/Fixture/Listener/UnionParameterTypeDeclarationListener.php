<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEvent2;
use Tests\Fixture\TestEventInterface;

final class UnionParameterTypeDeclarationListener
{
    public function __invoke(TestEvent|TestEvent2 $testEvent): void
    {
        unset($testEvent);
    }
}
