<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;

function listenerFunction(TestEvent $testEvent): void
{
    $testEvent->write(__METHOD__);
}
