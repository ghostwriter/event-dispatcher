<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Throwable;

final class ErrorEvent extends AbstractEvent implements ErrorEventInterface
{
    private EventInterface $event;

    /**
     * @var callable(EventInterface):void
     */
    private $listener;

    private Throwable $throwable;

    /**
     * @param callable(EventInterface):void $listener
     */
    public function __construct(EventInterface $event, callable $listener, Throwable $throwable)
    {
        $this->event = $event;
        $this->listener = $listener;
        $this->throwable = $throwable;
    }

    public function getEvent(): EventInterface
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
