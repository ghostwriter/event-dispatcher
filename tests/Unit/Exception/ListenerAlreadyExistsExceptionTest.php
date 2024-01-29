<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcherTests\Fixture\TestEvent;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventListener;
use Ghostwriter\EventDispatcherTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(ListenerAlreadyExistsException::class)]
final class ListenerAlreadyExistsExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testBind(): void
    {
        $this->expectException(ListenerAlreadyExistsException::class);

        $this->bind(TestEvent::class, TestEventListener::class, TestEventListener::class);
    }

    /**
     * @throws Throwable
     */
    public function testListen(): void
    {
        $this->expectException(ListenerAlreadyExistsException::class);

        $this->listen(TestEventListener::class, TestEventListener::class);
    }
}
