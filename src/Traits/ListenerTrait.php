<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Traits;

use Ghostwriter\EventDispatcher\Event;

trait ListenerTrait
{
    /**
     * @param callable $listener
     */
    public function __construct(
        private readonly mixed $listener
    ) {
    }

    /**
     * @param Event<bool> $event
     */
    public function __invoke(Event $event): void
    {
        ($this->listener)($event);
    }

    public function getListener(): callable
    {
        return $this->listener;
    }
}
