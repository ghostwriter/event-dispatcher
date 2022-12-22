<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\EventInterface;

final class Listener
{
    /**
     * @var callable(EventInterface<bool>):void
     */
    private $listener;

    /**
     * @param callable(EventInterface<bool>):void $listener
     */
    public function __construct(callable $listener)
    {
        $this->listener = $listener;
    }

    public function __invoke(EventInterface $event): void
    {
        ($this->listener)($event);
    }

    /**
     * @return callable(EventInterface<bool>):void
     */
    public function getListener(): callable
    {
        return $this->listener;
    }
}
