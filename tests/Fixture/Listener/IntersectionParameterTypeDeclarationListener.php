<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEvent2;

final class IntersectionParameterTypeDeclarationListener
{
    public function __invoke(TestEvent&TestEvent2 $testEvent): void
    {
    }
}
