<?php

declare(strict_types=1);

namespace Tests\Fixture;

interface TestEventInterface
{
    public function count(): int;

    public function read(): array;

    public function write(string $event): void;
}
