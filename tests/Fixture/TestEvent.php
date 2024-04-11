<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Ghostwriter\EventDispatcher\Trait\EventTrait;

/**
 * @template TStopPropagation of bool
 * @implements TestEventInterface<TStopPropagation>
 */
final class TestEvent implements TestEventInterface
{
    /** @use EventTrait<TStopPropagation> */
    use EventTrait;

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
