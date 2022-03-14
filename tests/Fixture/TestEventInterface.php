<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\Contract\EventInterface;

interface TestEventInterface extends EventInterface
{
    public function write(string $event): void;

    /** @return array<array-key,string> */
    public function read(): array;
}
