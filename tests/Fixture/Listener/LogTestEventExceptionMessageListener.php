<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorOccurredEventInterface;

final readonly class LogTestEventExceptionMessageListener
{
    public function __invoke(ErrorOccurredEventInterface $event): void
    {
        unset($event);
    }
}
