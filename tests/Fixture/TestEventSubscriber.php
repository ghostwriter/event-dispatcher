<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\ProviderInterface;
use Ghostwriter\EventDispatcher\SubscriberInterface;

final class TestEventSubscriber implements SubscriberInterface
{
    /**
     * @throws \Throwable
     */
    public function __invoke(ProviderInterface $provider): void
    {
        $provider->bind(
            TestEvent::class,
            TestEventListener::class,
            0,
            'InvokableListener'
        );

        $provider->listen(
            [new TestEventListener(), 'onTest'],
            0,
            TestEvent::class,
            'CallableArrayInstanceListener'
        );

        $provider->listen(
            static function (TestEventInterface $testEvent): void {
                $testEvent->write(__METHOD__);
            },
            0,
            TestEvent::class,
            'AnonymousFunctionListener'
        );

        $provider->listen(
            'Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction',
            0,
            TestEvent::class,
            'FunctionListener'
        );

        $provider->listen(
            TestEventListener::class . '::onStatic',
            0,
            TestEvent::class,
            'StaticMethodListener'
        );

        $provider->listen(
            [TestEventListener::class, 'onStaticCallableArray'],
            0,
            TestEvent::class,
            'CallableArrayStaticMethodListener'
        );
    }
}
