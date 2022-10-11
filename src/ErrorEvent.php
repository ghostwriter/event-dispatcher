<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Throwable;

/**
 * @template TEvent of object
 *
 * @implements ErrorEventInterface<TEvent>
 */
final class ErrorEvent extends AbstractEvent implements ErrorEventInterface
{
    /**
     * @var callable(TEvent):void
     */
    private $listener;

    /**
     * @param TEvent                $event
     * @param callable(TEvent):void $listener
     */
    public function __construct(
        private object $event,
        callable $listener,
        private Throwable $throwable
    ) {
        $this->listener = $listener;
    }

    public function getEvent(): object
    {
        return $this->event;
    }

    public function getListener(): callable
    {
        return $this->listener;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
