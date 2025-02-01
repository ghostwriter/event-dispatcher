<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\EventDispatcher\Container\ServiceProvider;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use Tests\Fixture\Listener\ErrorEventListener;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(ServiceProvider::class)]
final class ErrorEventTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testErrorEventComposesEventListenerAndThrowable(): void
    {
        self::assertSame($this->testEvent, $this->errorEvent->event());
        self::assertSame($this->listener, $this->errorEvent->listener());
        self::assertSame($this->throwable, $this->errorEvent->throwable());

        self::assertSame(self::ERROR_MESSAGE, $this->errorEvent->throwable()->getMessage());
        self::assertSame(self::ERROR_CODE, $this->errorEvent->throwable()->getCode());
    }

    /**
     * @throws Throwable
     */
    public function testErrorEventImplementsErrorEventInterface(): void
    {
        self::assertInstanceOf(ErrorEventInterface::class, $this->errorEvent);
    }

    /**
     * @throws Throwable
     */
    public function testErrorEventListenerThrowsRuntimeException(): void
    {
        $this->listenerProvider->bind(ErrorEvent::class, ErrorEventListener::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->expectExceptionCode(self::ERROR_CODE);

        $this->eventDispatcher->dispatch($this->errorEvent);
    }

    /**
     * @throws Throwable
     */
    public function testGetEvent(): void
    {
        self::assertSame($this->testEvent, $this->errorEvent->event());
    }

    /**
     * @throws Throwable
     */
    public function testGetListener(): void
    {
        self::assertSame($this->listener, $this->errorEvent->listener());
    }

    /**
     * @throws Throwable
     */
    public function testGetThrowable(): void
    {
        self::assertSame($this->throwable, $this->errorEvent->throwable());
    }
}
