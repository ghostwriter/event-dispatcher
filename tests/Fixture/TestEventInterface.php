<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\Interface\EventInterface;

/**
 * @template TStopPropagation of bool
 *
 * @extends EventInterface<TStopPropagation>
 */
interface TestEventInterface extends EventInterface
{
    /**
     * @return int<0,max>
     */
    public function count(): int;

    public function read(): string;

    public function write(string $event): void;
}
