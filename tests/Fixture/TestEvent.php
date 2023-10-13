<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\AbstractEvent;

/**
 * @template TStopPropagation of bool
 *
 * @extends AbstractEvent<TStopPropagation>
 *
 * @implements TestEventInterface<TStopPropagation>
 */
final class TestEvent extends AbstractEvent implements TestEventInterface
{
    /**
     * @var array<array-key,string>
     */
    private array $events = [];

    public function count(): int
    {
        return \count($this->events);
    }

    public function read(): string
    {
        return implode('|', $this->events);
    }

    public function write(string $event): void
    {
        $this->events[] = $event;
    }
}
