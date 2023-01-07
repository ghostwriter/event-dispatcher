<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

interface ListenerInterface
{
    public function __invoke(EventInterface $event): void;
}
