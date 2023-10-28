<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;

/**
 * @template TStopPropagation of bool
 * @implements EventInterface<TStopPropagation>
 */
final class TestEvent2 implements EventInterface
{
    /** @use EventTrait<TStopPropagation> */
    use EventTrait;
}
