<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\Subscriber\TestEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SubscriberAlreadyRegisteredException::class)]
#[CoversClass(ListenerProvider::class)]
final class SubscriberAlreadyRegisteredExceptionTest extends TestCase
{
    public function testThrowsSubscriberAlreadyRegisteredException(): void
    {

        $provider = new ListenerProvider();

        $this->expectException(SubscriberAlreadyRegisteredException::class);

        $provider->subscribe(TestEventSubscriber::class);
        $provider->subscribe(TestEventSubscriber::class);
    }
}

