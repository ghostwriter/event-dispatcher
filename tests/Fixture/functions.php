<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

function listenerFunction(TestEvent $testEvent): void
{
    $testEvent->write(__METHOD__);
}
