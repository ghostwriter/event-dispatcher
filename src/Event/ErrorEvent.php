<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Throwable;
use Override;

final readonly class ErrorEvent implements ErrorEventInterface
{
    /**
     * @template TEvent of object
     * @template TListener of object
     *
     * @param TEvent                                          $event
     * @param class-string<(callable(TEvent):void)&TListener> $listener
     */
    public function __construct(
        private object $event,
        private string $listener,
        private Throwable $throwable
    ) {}

    /**
     * @template TEvent of object
     *
     * @return TEvent
     */
    #[Override]
    public function getEvent(): object
    {
        return $this->event;
    }

    /**
     * @template TEvent of object
     * @template TListener of object
     *
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
