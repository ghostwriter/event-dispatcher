<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventInterface;

final readonly class LogTestEventExceptionMessageListener
{
    public function __invoke(ErrorEventInterface $event): void
    {
        /** @var TestEventInterface $testEvent */
        $testEvent = $event->getEvent();

        $testEvent->write($event->getThrowable()->getMessage());
    }
}
