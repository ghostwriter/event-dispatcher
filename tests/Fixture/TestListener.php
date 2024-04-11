<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Ghostwriter\EventDispatcher\Interface\EventInterface;

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

    public function __invoke(EventInterface $event): void
    {
        $this->called[] = $event::class;
    }
}
