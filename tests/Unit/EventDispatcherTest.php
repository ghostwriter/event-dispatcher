<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Tests\Fixture\Listener\BlackLivesMatterListener;
use Tests\Fixture\Listener\LogTestEventExceptionMessageListener;
use Tests\Fixture\Listener\TestEventRaiseAnExceptionListener;
use Tests\Fixture\Subscriber\TestEventSubscriber;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Fixture\TestListener;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
final class EventDispatcherTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testBlackLivesMatterListener(): void
    {
        self::assertEmpty($this->testEvent->read());

        $this->listenerProvider->listen(TestEventInterface::class, BlackLivesMatterListener::class);

        $this->dispatch($this->testEvent);

        self::assertCount(1, $this->testEvent->read());
    }

    /**
     * @template TEvent of object
     *
     * @param TEvent $event
     *
     * @throws Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testDispatch(object $event): void
    {
        self::assertInstanceOf(EventDispatcherInterface::class, $this->eventDispatcher);

        $this->dispatch($event);
    }

    /**
     * @throws Throwable
     */
    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        self::assertInstanceOf(EventDispatcherInterface::class, EventDispatcher::new());
    }

    /**
     * @throws Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAProvider(): void
    {
        self::assertEmpty($this->testEvent->read());

        $this->listenerProvider->subscribe(TestEventSubscriber::class);
        $this->assertListenersCount(1, $this->testEvent);

        $this->dispatch($this->testEvent);
        self::assertSame(1, $this->testEvent->count());

        $this->dispatch($this->testEvent);
        self::assertGreaterThan(1, $this->testEvent->count());

        $this->listenerProvider->unsubscribe(TestEventSubscriber::class);
        $this->assertListenersCount(0, $this->testEvent);

        self::assertCount(2, $this->testEvent->read());
    }

    /**
     * @throws Throwable
     */
    public function testSuppressTestEventRaiseAnExceptionListener(): void
    {
        $this->listenerProvider->listen(TestEvent::class, TestEventRaiseAnExceptionListener::class);

        $this->listenerProvider->listen(ErrorEventInterface::class, LogTestEventExceptionMessageListener::class);

        try {
            $this->dispatch($this->testEvent);

            self::fail('Expected an exception to be not thrown');
        } catch (Throwable $exception) {
            self::assertInstanceOf($this->throwable::class, $exception);
            self::assertSame($this->testEvent::class, $exception->getMessage());
        }
    }

    /**
     * @throws Throwable
     */
    public function testTestEventRaiseAnExceptionListener(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($this->testEvent::class);

        $this->listenerProvider->listen(TestEvent::class, TestEventRaiseAnExceptionListener::class);

        $this->dispatch($this->testEvent);
    }

    /**
     * @throws Throwable
     */
    public function testThrows(): void
    {
        $event = new TestEvent();
        $listener = TestListener::class;
        $throwable = new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);

        $errorEvent = new ErrorEvent($event, $listener, $throwable);
        //
        $this->expectException($throwable::class);
        $this->expectExceptionMessage($throwable->getMessage());
        $this->expectExceptionCode($throwable->getCode());

        $this->eventDispatcher->dispatch($errorEvent);

        throw $errorEvent->getThrowable();
    }
}
