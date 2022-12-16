<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\Contract\ListenerInterface;

final class TestEventListener implements ListenerInterface
{
    public function __invoke(TestEvent $testEvent): void
    {
        $testEvent->write(__METHOD__);
    }

    public function onTest(TestEvent $testEvent): void
    {
        $testEvent->write(__METHOD__);
    }

    public static function onStatic(TestEvent $testEvent): void
    {
        $testEvent->write(__METHOD__);
    }

    public static function onStaticCallableArray(TestEvent $testEvent): void
    {
        $testEvent->write(__METHOD__);
    }
}
