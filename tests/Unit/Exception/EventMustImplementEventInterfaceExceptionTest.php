<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(EventMustImplementEventInterfaceException::class)]
#[CoversClass(ListenerProvider::class)]
final class EventMustImplementEventInterfaceExceptionTest extends TestCase
{
    public function testThrowsEventMustImplementEventInterfaceException(): void
    {
        $provider = new ListenerProvider();

        $this->expectException(EventMustImplementEventInterfaceException::class);

        $provider->bind(stdClass::class, TestEventListener::class);
    }
}

