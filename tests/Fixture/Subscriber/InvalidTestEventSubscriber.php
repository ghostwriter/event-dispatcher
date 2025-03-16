<?php

declare(strict_types=1);

namespace Tests\Fixture\Subscriber;

use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Tests\Fixture\TestEventListener;
use Throwable;
use Tests\Fixture\TestEvent;

final readonly class InvalidTestEventSubscriber
{
    /**
     * @throws Throwable
     */
    public function __invoke(ListenerProviderInterface $provider): void
    {
        $provider->bind(TestEvent::class, TestEventListener::class);
    }
}
