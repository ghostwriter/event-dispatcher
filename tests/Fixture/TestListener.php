<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

final class TestListener
{
    private array $called = [];

    public function intersection(TestEvent&TestEvent2 $testEvent): void
    {
        $this->called[] = __METHOD__;
    }

    public function union(TestEvent|TestEvent2 $testEvent): void
    {
        $this->called[] = __METHOD__;
    }
}
