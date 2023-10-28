<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Throwable;

/**
 * @template TStopPropagation of bool
 * @implements ErrorEventInterface<TStopPropagation>
 */
final class ErrorEvent implements ErrorEventInterface
{
    /** @use EventTrait<TStopPropagation> */
    use EventTrait;
    /**
     * @param EventInterface<TStopPropagation> $event
     * @param callable(EventInterface<TStopPropagation>): void $listener
     */
    public function __construct(
        private readonly EventInterface $event,
        private readonly mixed $listener,
        private readonly Throwable $throwable
    ) {
    }

    /**
     * @return EventInterface<TStopPropagation>
     */
    public function getEvent(): EventInterface
    {
        return $this->event;
    }

    /**
     * @return callable(EventInterface<TStopPropagation>): void
     */
    public function getListener(): mixed
    {
        return $this->listener;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
