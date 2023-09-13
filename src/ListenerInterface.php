<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

interface ListenerInterface
{
    /**
     * @param EventInterface<bool> $event
     */
    public function __invoke(EventInterface $event): void;
}
