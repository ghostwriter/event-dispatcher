<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\Event;

/**
 * @template TPropagationStopped of bool
 *
 * @extends Event<TPropagationStopped>
 */
interface TestEventInterface extends Event
{
    public function write(string $event): void;

    /** @return array<array-key,string> */
    public function read(): array;

    /**
     * @return int<0,max>
     */
    public function count(): int;
}
