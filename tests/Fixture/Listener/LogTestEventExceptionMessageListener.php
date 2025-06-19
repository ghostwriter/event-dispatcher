<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Tests\Fixture\TestEventInterface;

final readonly class LogTestEventExceptionMessageListener
{
    public function __invoke(ErrorEventInterface $event): void
    {
        unset($event);
    }
}
