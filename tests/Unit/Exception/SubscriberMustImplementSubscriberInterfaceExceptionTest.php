<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Container\ServiceProvider;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\Subscriber\InvalidTestEventSubscriber;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(SubscriberMustImplementSubscriberInterfaceException::class)]
#[CoversClass(ServiceProvider::class)]
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
