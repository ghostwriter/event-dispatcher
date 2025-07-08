<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Container\ServiceProvider;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\Subscriber\TestEventSubscriber;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(ServiceProvider::class)]
#[CoversClass(SubscriberAlreadyRegisteredException::class)]
final class SubscriberAlreadyRegisteredExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testThrowsSubscriberAlreadyRegisteredException(): void
    {
        $this->expectException(SubscriberAlreadyRegisteredException::class);

        $this->listenerProvider->subscribe(TestEventSubscriber::class);
        $this->listenerProvider->subscribe(TestEventSubscriber::class);
    }
}
