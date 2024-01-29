<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Listener;

final class MissingEventParameterListener
{
    public function __invoke(): void
    {
    }
}
