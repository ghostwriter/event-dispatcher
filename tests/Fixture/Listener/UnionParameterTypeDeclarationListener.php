<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Listener;

use Ghostwriter\EventDispatcherTests\Fixture\TestEvent;
use Ghostwriter\EventDispatcherTests\Fixture\TestEvent2;

final class UnionParameterTypeDeclarationListener
{
    public function __invoke(TestEvent|TestEvent2 $testEvent): void
    {
    }
}
