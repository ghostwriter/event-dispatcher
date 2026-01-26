<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Override;

final class TestEvent implements TestEventInterface
{
    /**
     * @var array<array-key,string>
     */
    private array $events = [];

    #[Override]
        public function count(): int
    {
        return \count($this->events);
    }

    /**
     * @return array<array-key,string>
     */
    #[Override]
    public function read(): array
    {
        return $this->events;
    }

    #[Override]
    public function write(string $event): void
    {
        $this->events[] = $event;
    }
}
