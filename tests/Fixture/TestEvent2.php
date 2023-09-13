<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\EventInterface;

/**
 * @template TStopped of bool
 *
 * @extends AbstractEvent<TStopped>
 *
 * @implements EventInterface<TStopped>
 */
final class TestEvent2 extends AbstractEvent implements EventInterface
{
}
