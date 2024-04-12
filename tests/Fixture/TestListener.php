<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Ghostwriter\EventDispatcher\Interface\EventInterface;

final class TestListener
{
    private array $called = [];

    public function __invoke(EventInterface $event): void
    {
        $this->called[] = $event::class;
    }
}
