<?php

declare(strict_types=1);

namespace Tests\Fixture;
final class TestEventListener
{
    public function __invoke(TestEvent $testEvent): void
    {
        $testEvent->write(__METHOD__);
    }
}
