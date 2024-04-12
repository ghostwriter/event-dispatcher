<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Ghostwriter\EventDispatcher\Interface\EventInterface;

interface TestEventInterface extends EventInterface
{
    public function count(): int;

    public function read(): array;

    public function write(string $event): void;
}
