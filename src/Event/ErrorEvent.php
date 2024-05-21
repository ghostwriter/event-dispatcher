<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Throwable;
use Override;

/**
 * @template TEvent of object
 * @template TListener of object
 *
 * @implements ErrorEventInterface<TEvent,TListener>
 */
final readonly class ErrorEvent implements ErrorEventInterface
{
    /**
     * @param TEvent                                          $event
     * @param class-string<(callable(TEvent):void)&TListener> $listener
     */
    public function __construct(
        private object $event,
        private string $listener,
        private Throwable $throwable
    ) {}

    /**
     * @return TEvent
     */
    #[Override]
    public function getEvent(): object
    {
        return $this->event;
    }

    /**
     * @return class-string<(callable(TEvent):void)&TListener>
     */
    #[Override]
    public function getListener(): string
    {
        return $this->listener;
    }

    #[Override]
    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
