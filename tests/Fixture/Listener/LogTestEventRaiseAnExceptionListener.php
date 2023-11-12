<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;

final class LogTestEventRaiseAnExceptionListener
{
    public function __invoke(ErrorEventInterface $event): void
    {
        $event->getEvent()->write($event->getThrowable()->getMessage());
    }
}
