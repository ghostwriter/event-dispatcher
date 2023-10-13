<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Interface\EventInterface;

/**
 * @template TStopPropagation of bool
 *
 * @extends AbstractEvent<TStopPropagation>
 *
 * @implements EventInterface<TStopPropagation>
 */
final class TestEvent2 extends AbstractEvent implements EventInterface
{
}
