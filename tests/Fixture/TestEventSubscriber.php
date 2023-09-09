<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Fixture;

use Ghostwriter\EventDispatcher\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\SubscriberInterface;

final class TestEventSubscriber implements SubscriberInterface
{
    /**
     * @throws \Throwable
     */
    public function __invoke(ListenerProviderInterface $listenerProvider): void
    {
        $listenerProvider->bindListener(
            TestEvent::class,
            TestEventListener::class,
            0,
            'InvokableListener'
        );

        $listenerProvider->addListener(
            [new TestEventListener(), 'onTest'],
            0,
            TestEvent::class,
            'CallableArrayInstanceListener'
        );

        $listenerProvider->addListener(
            static function (TestEventInterface $testEvent): void {
                $testEvent->write(__METHOD__);
            },
            0,
            TestEvent::class,
            'AnonymousFunctionListener'
        );

        $listenerProvider->addListener(
            'Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction',
            0,
            TestEvent::class,
            'FunctionListener'
        );

        $listenerProvider->addListener(
            TestEventListener::class.'::onStatic',
            0,
            TestEvent::class,
            'StaticMethodListener'
        );

        $listenerProvider->addListener(
            [TestEventListener::class, 'onStaticCallableArray'],
            0,
            TestEvent::class,
            'CallableArrayStaticMethodListener'
        );
    }
}
