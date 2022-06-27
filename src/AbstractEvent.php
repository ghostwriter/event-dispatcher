<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use DateTimeImmutable;
use Ghostwriter\EventDispatcher\Contract\EventInterface;

abstract class AbstractEvent implements EventInterface
{
    private ?DateTimeImmutable $dateTimePropagationStopped = null;

    public function getDateTimePropagationStopped(): ?DateTimeImmutable
    {
        return $this->dateTimePropagationStopped;
    }

    public function isPropagationStopped(): bool
    {
        return $this->dateTimePropagationStopped instanceof DateTimeImmutable;
    }

    public function stopPropagation(): void
    {
        $this->dateTimePropagationStopped = new DateTimeImmutable();
    }
}
