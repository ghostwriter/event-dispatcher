<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture\Listener;

use Ghostwriter\EventDispatcher\ErrorEvent;

final class MissingInvokeMethodListener
{
    public static function onError(ErrorEvent $event): void
    {
        // Raise an exception
        throw new \RuntimeException($event::class);
    }
}
