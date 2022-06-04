<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Throwable;

final class ErrorEvent extends AbstractEvent implements ErrorEventInterface
{
    /**
     * @var callable(EventInterface):void
     */
    private $listener;

    /**
     * @param callable(EventInterface):void $listener
     */
    public function __construct(private EventInterface $event, callable $listener, private Throwable $throwable)
    {
        $this->listener = $listener;
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
