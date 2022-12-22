<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Traits\EventTrait;
use Throwable;

/**
 * @template TPropagationStopped of bool
 *
 * @implements ErrorEventInterface<TPropagationStopped>
 */
final class ErrorEvent implements ErrorEventInterface
{
    use EventTrait;

    public function __construct(
        private readonly EventInterface $event,
        private readonly Listener $listener,
        private readonly Throwable $throwable
    ) {
    }

    public function getEvent(): EventInterface
    {
        return $this->event;
    }

    public function getListener(): Listener
    {
        return $this->listener;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
