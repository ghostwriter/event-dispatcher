<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture\Subscriber;

use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
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
