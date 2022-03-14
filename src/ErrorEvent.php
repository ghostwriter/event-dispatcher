<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Throwable;

final class ErrorEvent extends AbstractEvent implements ErrorEventInterface
{
    private object $event;

    /** @var callable */
    private $listener;

    private Throwable $throwable;

    public function __construct(object $event, callable $listener, Throwable $throwable)
    {
        $this->event = $event;
        $this->throwable = $throwable;
        $this->listener  = $listener;
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
