<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture;

function listenerFunction(TestEvent $testEvent): void
{
    $testEvent->write(__METHOD__);
}
