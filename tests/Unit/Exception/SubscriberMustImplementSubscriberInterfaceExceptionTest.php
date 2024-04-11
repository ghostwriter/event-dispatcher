<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcherTests\Fixture\Subscriber\InvalidTestEventSubscriber;
use Ghostwriter\EventDispatcherTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(SubscriberMustImplementSubscriberInterfaceException::class)]
final class SubscriberMustImplementSubscriberInterfaceExceptionTest extends AbstractTestCase
{
    /**
     * @psalm-suppress InvalidArgument
     *
     * @throws Throwable
     */
    public function testThrowsSubscriberMustImplementSubscriberInterfaceException(): void
    {
        $this->expectException(SubscriberMustImplementSubscriberInterfaceException::class);

        $this->listenerProvider->subscribe(InvalidTestEventSubscriber::class);
    }
}
