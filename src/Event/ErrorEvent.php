<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Event;

use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Throwable;

/**
 * @template TStopPropagation of bool
 *
 * @implements ErrorEventInterface<TStopPropagation>
 */
final class ErrorEvent implements ErrorEventInterface
{
    /**
     * @use EventTrait<TStopPropagation>
     */
    use EventTrait;

    /**
     * @param EventInterface<TStopPropagation>                                     $event
     * @param class-string<callable(EventInterface<TStopPropagation>):void&object> $listener
     */
    public function __construct(
        private readonly EventInterface $event,
        private readonly string $listener,
        private readonly Throwable $throwable
    ) {}

    /**
     * @return EventInterface<TStopPropagation>
     */
    public function getEvent(): EventInterface
    {
        return $this->event;
    }

    /**
     * @return class-string<callable(EventInterface<TStopPropagation>):void&object>
     */
    public function getListener(): string
    {
        return $this->listener;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
