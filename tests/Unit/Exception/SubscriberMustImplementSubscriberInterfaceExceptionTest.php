<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\Subscriber\InvalidTestEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
#[CoversClass(SubscriberMustImplementSubscriberInterfaceException::class)]
#[CoversClass(ListenerProvider::class)]
final class SubscriberMustImplementSubscriberInterfaceExceptionTest extends TestCase
{
    public function testThrowsSubscriberMustImplementSubscriberInterfaceException(): void
    {

        $provider = new ListenerProvider();

        $this->expectException(SubscriberMustImplementSubscriberInterfaceException::class);

        $provider->subscribe(InvalidTestEventSubscriber::class);
    }
}
