<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

interface Listener
{
    /**
     * @param Event<bool> $event
     */
    public function __invoke(Event $event): void;
}
