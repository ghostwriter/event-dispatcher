<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture\Listener;

final class MissingEventParameterListener
{
    public function __invoke(): void
    {
    }
}
