<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\Event;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventListenerProvider;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventSubscriber;
use Ghostwriter\EventDispatcher\Traits\EventTrait;
use Ghostwriter\EventDispatcher\Traits\ListenerTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(EventListenerProvider::class)]
#[CoversClass(ListenerTrait::class)]
#[Small]
final class DispatcherTest extends PHPUnitTestCase
{
    /**
     * @var int
     */
    public const ERROR_CODE = 42;

    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Simulate error raised while processing an event!';

    private Dispatcher $dispatcher;

    private ListenerProvider $listenerProvider;

    protected function setUp(): void
    {
        $this->listenerProvider = new EventListenerProvider();
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * @return \Generator<string,list<Event>>
     */
    public static function eventDataProvider(): \Generator
    {
        yield Event::class => [
            /* @extends Event<bool> */
            new class() implements Event {
                use EventTrait;
            },
        ];

        yield ErrorEvent::class => [new ErrorEvent(
            new TestEvent(),
            new class(static fn (): mixed => null) implements Listener {
                use ListenerTrait;
            },
            new \RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE)
        )];

        yield TestEventInterface::class => [new TestEvent()];
    }

    /**
     * @param Event<bool> $event
     */
    #[DataProvider('eventDataProvider')]
    public function testAlreadyStoppedEventCallsNoListeners(Event $event): void
    {
        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        self::assertTrue($event->isPropagationStopped());

        $this->dispatcher
            ->getListenerProvider()
            ->addListener(static function (Event $event): never {
                throw new \RuntimeException(self::ERROR_MESSAGE.$event::class, self::ERROR_CODE);
            });

        self::assertSame($event, $this->dispatcher->dispatch($event));
        self::assertTrue($event->isPropagationStopped());
    }

    public function testConstruct(ListenerProvider $listenerProvider = null): void
    {
        $dispatcher = new EventDispatcher($listenerProvider ?? new EventListenerProvider());
        self::assertInstanceOf(Dispatcher::class, $dispatcher);
    }

    /**
     * @param Event<bool> $event
     */
    #[DataProvider('eventDataProvider')]
    public function testDispatch(Event $event): void
    {
        self::assertInstanceOf(Dispatcher::class, $this->dispatcher);
        self::assertSame($event, $this->dispatcher->dispatch($event));
    }

    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        self::assertInstanceOf(Dispatcher::class, $this->dispatcher);
    }

    /**
     * @throws \Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAListenerProvider(): void
    {
        $this->listenerProvider->addSubscriber(TestEventSubscriber::class);
        $this->dispatcher = new EventDispatcher($this->listenerProvider);

        $testEvent = new TestEvent();
        self::assertEmpty($testEvent->read());

        $testEventResult = $this->dispatcher->dispatch($testEvent);

        self::assertSame($testEvent, $testEventResult);
        self::assertCount(
            $testEvent->count(),
            iterator_to_array($this->listenerProvider->getListenersForEvent($testEvent))
        );
    }

    /**
     * @throws \Throwable
     */
    public function testMustListeners(): void
    {
        $testEvent = new TestEvent();
        $this->listenerProvider->addSubscriber(TestEventSubscriber::class);

        /**
         * @var callable(Event<bool>):void $listener
         */
        $listener = static function (
            /*
             * @param Event<bool> $testEvent
             */
            TestEventInterface $testEvent
        ): void {
            $testEvent->write(sprintf('%s', $testEvent->count()));
            $testEvent->stopPropagation();
        };

        $this->listenerProvider->addListener($listener, 1, TestEvent::class, __METHOD__.'Listener');

        $this->dispatcher = new EventDispatcher($this->listenerProvider);

        self::assertEmpty($testEvent->read());
        self::assertSame($testEvent, $this->dispatcher->dispatch($testEvent));
        self::assertNotEmpty($testEvent->read());
    }

    /**
     * @param Event<bool> $event
     *
     * @throws \Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(Event $event): void
    {
        $this->listenerProvider->addListener(static function (Event $event): never {
            throw new \RuntimeException(sprintf('Simulate error raised while processing "%s"; PsrStoppableEventInterface!', $event::class));
        });

        self::assertSame($event, $this->dispatcher->dispatch($event));
    }

    /**
     * @throws \Throwable
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

        self::assertInstanceOf(Dispatcher::class, $this->dispatcher);
    }
}
