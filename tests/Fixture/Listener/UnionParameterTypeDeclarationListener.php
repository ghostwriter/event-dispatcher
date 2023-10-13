<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent2;

final class UnionParameterTypeDeclarationListener
{
    public function __invoke(TestEvent|TestEvent2 $testEvent): void
    {
    }
}
