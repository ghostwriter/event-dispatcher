<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Traits;

trait EventTrait
{
    private bool $stopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    public function stopPropagation(): void
    {
        $this->stopped = true;
    }
}
