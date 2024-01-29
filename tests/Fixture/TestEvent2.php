<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture;

use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventInterface;
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
