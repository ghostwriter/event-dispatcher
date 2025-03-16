<?php

declare(strict_types=1);

namespace Tests\Fixture;

final class TestListener
{
    private array $called = [];

    public function __invoke(object $event): void
    {
        $this->called[] = $event::class;
    }

    public function called(): array
    {
        return $this->called;
    }
}
