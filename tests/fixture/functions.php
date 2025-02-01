<?php

declare(strict_types=1);

namespace Tests\Fixture;

function listenerFunction(TestEvent $testEvent): void
{
    $testEvent->write(__METHOD__);
}
