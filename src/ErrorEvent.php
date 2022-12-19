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

    /**
     * @var callable(EventInterface<bool>):void
     */
    private $listener;

    /**
     * @param callable(EventInterface<bool>):void $listener
     */
    public function __construct(
        private EventInterface $event,
        callable $listener,
        private Throwable $throwable
    ) {
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
