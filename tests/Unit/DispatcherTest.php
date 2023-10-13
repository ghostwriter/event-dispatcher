<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Generator;
use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\ErrorEvent;
use Ghostwriter\EventDispatcher\Interface\DispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\AlreadyStoppedEventCallsNoListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\BlackLivesMatterListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\ReturnsEventWithoutResolvingListenersIfPropagationIsStoppedListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\LogTestEventRaiseAnExceptionListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\TestEventRaiseAnExceptionListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\Subscriber\TestEventSubscriber;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

#[CoversClass(AbstractEvent::class)]
#[CoversClass(Dispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[Small]
final class DispatcherTest extends TestCase
{
    /**
     * @var int
     */
    public const ERROR_CODE = 42;

    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Simulate error raised while processing an event!';

    private DispatcherInterface $dispatcher;

    private ListenerProviderInterface $provider;

    protected function setUp(): void
    {
        $this->provider = new ListenerProvider();
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @return Generator<string,list<EventInterface>>
     */
    public static function eventDataProvider(): Generator
    {
        yield EventInterface::class => [
            new /**
 * @extends AbstractEvent<bool>
*/ class() extends AbstractEvent {
},
        ];

        yield ErrorEvent::class => [
            new ErrorEvent(
                new TestEvent(),
                static fn (EventInterface $event): mixed => $event,
                new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE)
            ),
        ];

        yield TestEventInterface::class => [new TestEvent()];
    }

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

        $provider = new ListenerProvider();

        $provider->listen(AlreadyStoppedEventCallsNoListener::class);

        $dispatcher = new Dispatcher($provider);

        self::assertSame($event, $dispatcher->dispatch($event));
        self::assertTrue($event->isPropagationStopped());
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(
            DispatcherInterface::class,
            new Dispatcher(
                new ListenerProvider()
            )
        );
    }

    /**
     * @param EventInterface<bool> $event
     */
    #[DataProvider('eventDataProvider')]
    public function testDispatch(EventInterface $event): void
    {
        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
        self::assertSame($event, $this->dispatcher->dispatch($event));
    }

    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }

    /**
     * @throws Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAProvider(): void
    {
        $this->provider->subscribe(TestEventSubscriber::class);

        $this->dispatcher = new Dispatcher($this->provider);

        $testEvent = new TestEvent();
        self::assertSame('', $testEvent->read());

        $testEventResult = $this->dispatcher->dispatch($testEvent);

        self::assertSame($testEvent, $testEventResult);
        self::assertCount(
            $testEvent->count(),
            iterator_to_array($this->provider->getListenersForEvent($testEvent))
        );
    }

    /**
     * @throws Throwable
     */
    public function testBlackLivesMatterListener(): void
    {
        $testEvent = new TestEvent();

        $this->provider->listen(BlackLivesMatterListener::class);

        $this->dispatcher = new Dispatcher($this->provider);

        self::assertSame('', $testEvent->read());

        self::assertSame($testEvent, $this->dispatcher->dispatch($testEvent));

        self::assertSame('#BlackLivesMatter', $testEvent->read());
    }
    /**
     * @throws Throwable
     */
    public function testTestEventRaiseAnExceptionListener(): void
    {
        $testEvent = new TestEvent();

        $this->provider->listen(TestEventRaiseAnExceptionListener::class);

        $dispatcher = new Dispatcher($this->provider);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(TestEvent::class);
        $dispatcher->dispatch($testEvent);
    }
    /**
     * @throws Throwable
     */
    public function testSuppressTestEventRaiseAnExceptionListener(): void
    {
        $testEvent = new TestEvent();

        $this->provider->listen(TestEventRaiseAnExceptionListener::class);
        $this->provider->listen(LogTestEventRaiseAnExceptionListener::class);

        $dispatcher = new Dispatcher($this->provider);

        try {
            $testEvent = $dispatcher->dispatch($testEvent);

            self::fail('Expected exception not thrown');
        } catch (Throwable $th) {
            self::assertInstanceOf(RuntimeException::class, $th);
            self::assertSame(TestEvent::class, $th->getMessage());

            self::assertSame($th->getMessage(), $testEvent->read());
        }
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @throws Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(EventInterface $event): void
    {
        $this->provider->listen(ReturnsEventWithoutResolvingListenersIfPropagationIsStoppedListener::class);

        self::assertSame($event, $this->dispatcher->dispatch($event));
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

        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }
}
