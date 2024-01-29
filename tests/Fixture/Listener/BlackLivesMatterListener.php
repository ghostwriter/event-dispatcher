<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventInterface;
use RuntimeException;

final class BlackLivesMatterListener
{
    public function __invoke(TestEventInterface $event): void
    {
        $event->write('#BlackLivesMatter');

        $event->stopPropagation();
    }
}
