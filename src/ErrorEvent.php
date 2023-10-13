<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Interface\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerInterface;
use Throwable;

/**
 * @template TStopPropagation of bool
 *
 * @extends AbstractEvent<TStopPropagation>
 *
 * @implements ErrorEventInterface<TStopPropagation>
 */
final class ErrorEvent extends AbstractEvent implements ErrorEventInterface
{
    /**
     * @param EventInterface<TStopPropagation> $event
     */
    public function __construct(
        private readonly EventInterface $event,
        private readonly string|object $listener,
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

    public function getListener(): string|object
    {
        return $this->listener;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
