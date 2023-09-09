<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\Traits\EventTrait;

/**
 * @template TPropagationStopped of bool
 *
 * @implements TestEventInterface<TPropagationStopped>
 */
final class TestEvent implements TestEventInterface
{
    use EventTrait;

    /** @var array<array-key,string> */
    private array $events = [];

    public function write(string $event): void
    {
        $this->events[] = $event;
    }

    public function read(): string
    {
        return json_encode($this->events);
    }

    public function count(): int
    {
        return \count($this->events);
    }
}
