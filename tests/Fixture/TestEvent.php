<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Contract\EventInterface;

final class TestEvent extends AbstractEvent implements TestEventInterface
{
    /** @var array<array-key,string> */
    private array $events = [];

    public function write(string $event): void
    {
        $this->events[] = $event;
    }

    /** @return array<array-key,string> */
    public function read(): array
    {
        return $this->events;
    }
}
