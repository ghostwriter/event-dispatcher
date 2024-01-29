<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Interface\EventInterface;
use RuntimeException;

final class AlreadyStoppedEventCallsNoListener
{
    /**
     * @var int
     */
    public const ERROR_CODE = 42;

    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Simulate error raised while processing an event!';

    public function __invoke(EventInterface $event): void
    {
        throw new RuntimeException(self::ERROR_MESSAGE . $event::class, self::ERROR_CODE);
    }
}
