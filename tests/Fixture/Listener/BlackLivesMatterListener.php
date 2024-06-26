<?php

declare(strict_types=1);

namespace Tests\Fixture\Listener;

use Tests\Fixture\TestEventInterface;
use RuntimeException;

final class BlackLivesMatterListener
{
    public function __invoke(TestEventInterface $event): void
    {
        $event->write('#BlackLivesMatter');
    }
}
