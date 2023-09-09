<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

final readonly class Listener implements ListenerInterface
{
    /**
     * @param callable $listener
     */
    public function __construct(
        private readonly mixed $listener
    ) {
    }

    /**
     * @param EventInterface<bool> $event
     */
    public function __invoke(EventInterface $event): void
    {
        ($this->listener)($event);
    }

    public function getListener(): callable
    {
        return $this->listener;
    }
}
