<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcherTests\Fixture\Subscriber\TestEventSubscriber;
use Ghostwriter\EventDispatcherTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(SubscriberAlreadyRegisteredException::class)]
#[CoversClass(ListenerProvider::class)]
final class SubscriberAlreadyRegisteredExceptionTest extends AbstractTestCase
{
    public function testThrowsSubscriberAlreadyRegisteredException(): void
    {
        $provider = new ListenerProvider();

        $this->expectException(SubscriberAlreadyRegisteredException::class);

        $provider->subscribe(TestEventSubscriber::class);
        $provider->subscribe(TestEventSubscriber::class);
    }
}
