<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Listener;

use Ghostwriter\EventDispatcher\Interface\EventInterface;
use RuntimeException;

final class ReturnsEventWithoutResolvingListenersIfPropagationIsStoppedListener
{
    /**
     * @var int
     */
    public const ERROR_CODE = 42;

    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Simulate error raised while processing "%s"; PsrStoppableEventInterface!';

    public function __invoke(EventInterface $event): void
    {
        throw new RuntimeException(
            sprintf(
                self::ERROR_MESSAGE,
                $event::class
            ),
            self::ERROR_CODE
        );
    }
}
