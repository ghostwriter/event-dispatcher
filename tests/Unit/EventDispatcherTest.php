<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\AlreadyStoppedEventCallsNoListener;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\BlackLivesMatterListener;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\LogTestEventExceptionMessageListener;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\ReturnsEventWithoutResolvingListenersIfPropagationIsStoppedListener;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\TestEventRaiseAnExceptionListener;
use Ghostwriter\EventDispatcherTests\Fixture\Subscriber\TestEventSubscriber;
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

        $this->listen(AlreadyStoppedEventCallsNoListener::class)
            ->dispatch($event);

        self::assertTrue($event->isPropagationStopped());
    }

    /**
     * @throws Throwable
     */
    public function testBlackLivesMatterListener(): void
    {
        self::assertSame('', $this->testEvent->read());

        $this->listen(BlackLivesMatterListener::class)->dispatch($this->testEvent);

        self::assertSame('#BlackLivesMatter', $this->testEvent->read());
    }

    /**
     * @param EventInterface<bool> $event
     */
    #[DataProvider('eventDataProvider')]
    public function testDispatch(EventInterface $event): void
    {
        self::assertInstanceOf(EventDispatcherInterface::class, $this->eventDispatcher);
        self::assertSame($event, $this->eventDispatcher->dispatch($event));
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

        $this->subscribe(TestEventSubscriber::class)->dispatch($this->testEvent);

        self::assertListenersCount($this->testEvent->count(), $this->testEvent);

        self::assertSame('', $this->testEvent->read());
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @throws Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(EventInterface $event): void
    {
        $this->listen(ReturnsEventWithoutResolvingListenersIfPropagationIsStoppedListener::class);

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
        $this->listen(TestEventRaiseAnExceptionListener::class, LogTestEventExceptionMessageListener::class);

        try {
            $this->eventDispatcher->dispatch($this->testEvent);

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

        $this->listen(TestEventRaiseAnExceptionListener::class)->dispatch($this->testEvent);
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
