<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Tests\Fixture\Listener\AlreadyStoppedEventCallsNoListener;
use Tests\Fixture\Listener\BlackLivesMatterListener;
use Tests\Fixture\Listener\LogTestEventExceptionMessageListener;
use Tests\Fixture\Listener\ReturnsEventWithoutResolvingListenersIfPropagationIsStoppedListener;
use Tests\Fixture\Listener\TestEventRaiseAnExceptionListener;
use Tests\Fixture\Subscriber\TestEventSubscriber;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
final class EventDispatcherTest extends AbstractTestCase
{
    /**
     * @param EventInterface<bool> $event
     *
     * @throws Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testAlreadyStoppedEventCallsNoListeners(EventInterface $event): void
    {
        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        self::assertTrue($event->isPropagationStopped());

        $this->listenerProvider->listen(EventInterface::class, AlreadyStoppedEventCallsNoListener::class);

        $this->dispatch($event);

        self::assertTrue($event->isPropagationStopped());
    }

    /**
     * @throws Throwable
     */
    public function testBlackLivesMatterListener(): void
    {
        self::assertSame('', $this->testEvent->read());

        $this->listenerProvider->listen(TestEventInterface::class, BlackLivesMatterListener::class);

        $this->dispatch($this->testEvent);

        self::assertSame('#BlackLivesMatter', $this->testEvent->read());
    }

    /**
     * @param EventInterface<bool> $event
     */
    #[DataProvider('eventDataProvider')]
    public function testDispatch(EventInterface $event): void
    {
        self::assertInstanceOf(EventDispatcherInterface::class, $this->eventDispatcher);

        $this->dispatch($event);
    }

    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        self::assertInstanceOf(EventDispatcherInterface::class, $this->eventDispatcher);
    }

    /**
     * @throws Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAProvider(): void
    {
        self::assertSame('', $this->testEvent->read());

        $this->listenerProvider->subscribe(TestEventSubscriber::class);

        $this->dispatch($this->testEvent);

        //        self::assertListenersCount($this->testEvent->count(), $this->testEvent);

        //        self::assertSame('', $this->testEvent->read());
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @throws Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(EventInterface $event): void
    {
        $this->listenerProvider->listen(
            EventInterface::class,
            ReturnsEventWithoutResolvingListenersIfPropagationIsStoppedListener::class
        );

        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        self::assertTrue($event->isPropagationStopped());

        $this->assertListenersCount(0, $event);
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
            self::assertSame($this->testEvent::class, $this->testEvent->read());
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
    #[DataProvider('eventDataProvider')]
    public function testThrows(object $event): void
    {
        if ($event instanceof ErrorEvent) {
            $throwable = $event->getThrowable();

            $this->expectException($throwable::class);
            $this->expectExceptionMessage($throwable->getMessage());
            $this->expectExceptionCode($throwable->getCode());

            throw $throwable;
        }

        self::assertInstanceOf(EventDispatcherInterface::class, $this->eventDispatcher);
    }
}
