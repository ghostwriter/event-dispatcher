<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Fixture\Subscriber;

use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Interface\SubscriberInterface;
use Ghostwriter\EventDispatcherTests\Fixture\TestEvent;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventListener;
use Throwable;

final readonly class TestEventSubscriber implements SubscriberInterface
{
    /**
     * @throws Throwable
     */
    public function __invoke(ListenerProviderInterface $provider): void
    {
        // Invokable class '::__invoke'
//                $provider->bind(TestEvent::class, TestEventListener::class);
        $provider->listen(TestEventListener::class);

        // Invokable function
        //        $provider->bind(TestEvent::class, 'Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction');
//        $provider->listen('Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction');

        // Invokable static method
        //        $provider->bind(TestEvent::class, TestEventListener::class . '::onStatic');
//        $provider->listen(TestEventListener::class . '::onStatic');
    }
}
