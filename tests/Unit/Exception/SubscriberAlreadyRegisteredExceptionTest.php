<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Tests\Fixture\Subscriber\TestEventSubscriber;
use Tests\Unit\AbstractTestCase;
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
        $this->expectException(SubscriberAlreadyRegisteredException::class);

        $this->listenerProvider->subscribe(TestEventSubscriber::class);
        $this->listenerProvider->subscribe(TestEventSubscriber::class);
    }
}
