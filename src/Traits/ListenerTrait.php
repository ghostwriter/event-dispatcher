<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Traits;

use Closure;
use Ghostwriter\EventDispatcher\Contract\EventInterface;

trait ListenerTrait
{
    /**
     * @param callable $listener
     */
    public function __construct(
        private readonly mixed $listener
    ) {
    }

    public function __invoke(EventInterface $event): void
    {
        ($this->listener)($event);
    }

    public function getListener(): callable
    {
        return $this->listener;
    }
}
