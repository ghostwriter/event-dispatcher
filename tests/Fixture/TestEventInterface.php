<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\EventInterface;

/**
 * @template TPropagationStopped of bool
 *
 * @extends EventInterface<TPropagationStopped>
 */
interface TestEventInterface extends EventInterface
{
    public function write(string $event): void;

    public function read(): string;

    /**
     * @return int<0,max>
     */
    public function count(): int;
}
