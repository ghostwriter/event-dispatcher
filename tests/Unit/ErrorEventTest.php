<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\ErrorEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
final class ErrorEventTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testErrorEventComposesEventListenerAndThrowable(): void
    {
        self::assertSame($this->testEvent, $this->errorEvent->getEvent());
        self::assertSame($this->listener, $this->errorEvent->getListener());
        self::assertSame($this->throwable, $this->errorEvent->getThrowable());

        self::assertSame(self::ERROR_MESSAGE, $this->errorEvent->getThrowable()->getMessage());
        self::assertSame(self::ERROR_CODE, $this->errorEvent->getThrowable()->getCode());

        $this->listenerProvider->listen(ErrorEventListener::class);

        $this->eventDispatcher = EventDispatcher::new($this->listenerProvider);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->expectExceptionCode(self::ERROR_CODE);

        $this->eventDispatcher->dispatch($this->errorEvent);
    }

    public function testErrorEventImplementsErrorEventInterface(): void
    {
        self::assertInstanceOf(ErrorEventInterface::class, $this->errorEvent);
        self::assertInstanceOf(EventInterface::class, $this->errorEvent);
    }
}
