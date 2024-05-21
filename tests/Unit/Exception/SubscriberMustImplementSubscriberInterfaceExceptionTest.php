<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Tests\Fixture\Subscriber\InvalidTestEventSubscriber;
use Tests\Unit\AbstractTestCase;
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
     * @throws Throwable
     */
    public function testThrowsSubscriberMustImplementSubscriberInterfaceException(): void
    {
        $this->expectException(SubscriberMustImplementSubscriberInterfaceException::class);

        $this->listenerProvider->subscribe(InvalidTestEventSubscriber::class);
    }
}
