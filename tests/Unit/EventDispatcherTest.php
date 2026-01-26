<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\EventDispatcher\Container\EventDispatcherDefinition;
use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorOccurredEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Fixture\Listener\BlackLivesMatterListener;
use Tests\Fixture\Listener\LogTestEventExceptionMessageListener;
use Tests\Fixture\Listener\TestEventRaiseAnExceptionListener;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventInterface;
use Tests\Fixture\TestEventListener;
use Tests\Fixture\TestListener;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorOccurredEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(EventDispatcherDefinition::class)]
final class EventDispatcherTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testBlackLivesMatterListener(): void
    {
        self::assertEmpty($this->testEvent->read());

        $this->listenerProvider->listen(TestEventInterface::class, BlackLivesMatterListener::class);

        $this->dispatch($this->testEvent);

        self::assertCount(1, $this->testEvent->read());
    }

    /**
     * @template Event of object
     *
     * @param Event $event
     *
     * @throws Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testDispatch(object $event): void
    {
        self::assertInstanceOf(EventDispatcherInterface::class, $this->eventDispatcher);

        $this->dispatch($event);
    }

    /** @throws Throwable */
    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        self::assertInstanceOf(EventDispatcherInterface::class, EventDispatcher::new());
    }

    /** @throws Throwable */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAProvider(): void
    {
        self::assertEmpty($this->testEvent->read());

        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);
        $this->listenerProvider->listen(
            ErrorOccurredEventInterface::class,
            LogTestEventExceptionMessageListener::class
        );

        $this->assertListenersCount(1, $this->testEvent);

        $this->dispatch($this->testEvent);
        self::assertSame(1, $this->testEvent->count());

        $this->dispatch($this->testEvent);
        self::assertGreaterThan(1, $this->testEvent->count());

        $this->listenerProvider->remove(TestEventListener::class);
        $this->listenerProvider->remove(LogTestEventExceptionMessageListener::class);

        $this->assertListenersCount(0, $this->testEvent);

        self::assertCount(2, $this->testEvent->read());
    }

    /** @throws Throwable */
    public function testSuppressTestEventRaiseAnExceptionListener(): void
    {
        $this->listenerProvider->listen(TestEvent::class, TestEventRaiseAnExceptionListener::class);

        $this->listenerProvider->listen(
            ErrorOccurredEventInterface::class,
            LogTestEventExceptionMessageListener::class
        );

        try {
            $this->dispatch($this->testEvent);

            self::fail('Expected an exception to be not thrown');
        } catch (Throwable $throwable) {
            self::assertInstanceOf($this->throwable::class, $throwable);
            self::assertSame($this->testEvent::class, $throwable->getMessage());
        }
    }

    /** @throws Throwable */
    public function testTestEventRaiseAnExceptionListener(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($this->testEvent::class);

        $this->listenerProvider->listen(TestEvent::class, TestEventRaiseAnExceptionListener::class);

        $this->dispatch($this->testEvent);
    }

    /** @throws Throwable */
    public function testThrows(): void
    {
        $listener = TestListener::class;
        $runtimeException = new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);

        $errorEvent = new ErrorOccurredEvent($this->testEvent, $listener, $runtimeException);

        $this->expectException($runtimeException::class);
        $this->expectExceptionMessage($runtimeException->getMessage());
        $this->expectExceptionCode($runtimeException->getCode());

        $this->eventDispatcher->dispatch($errorEvent);

        throw $errorEvent->throwable();
    }
}
