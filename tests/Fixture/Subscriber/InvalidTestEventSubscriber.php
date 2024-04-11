<?php

declare(strict_types=1);

namespace Tests\Fixture\Subscriber;

use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Tests\Fixture\TestEventListener;
use Throwable;

final readonly class InvalidTestEventSubscriber
{
    /**
     * @throws Throwable
     */
    public function __invoke(ListenerProviderInterface $provider): void
    {
        $provider->listen(TestEventListener::class);
    }
}
