<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\DispatcherInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\ListenerInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventSubscriber;
use Ghostwriter\EventDispatcher\Traits\EventTrait;
use Ghostwriter\EventDispatcher\Traits\ListenerTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[CoversClass(Dispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(Listener::class)]
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

    private DispatcherInterface $dispatcher;

    private ListenerProviderInterface $listenerProvider;

    protected function setUp(): void
    {
        $this->listenerProvider = new ListenerProvider();
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @return \Generator<string,list<EventInterface>>
     */
    public static function eventDataProvider(): \Generator
    {
        yield EventInterface::class => [
            /* @extends EventInterface<bool> */
            new class () implements EventInterface {
                use EventTrait;
            },
        ];

        yield ErrorEvent::class => [new ErrorEvent(
            new TestEvent(),
            new Listener(static fn (): mixed => null),
            new \RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE)
        )];

        yield TestEventInterface::class => [new TestEvent()];
    }

    /**
     * @param EventInterface<bool> $event
     */
    #[DataProvider('eventDataProvider')]
    public function testAlreadyStoppedEventCallsNoListeners(EventInterface $event): void
    {
        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        self::assertTrue($event->isPropagationStopped());

        $this->dispatcher
            ->getListenerProvider()
            ->addListener(static function (EventInterface $event): never {
                throw new \RuntimeException(self::ERROR_MESSAGE.$event::class, self::ERROR_CODE);
            });

        self::assertSame($event, $this->dispatcher->dispatch($event));
        self::assertTrue($event->isPropagationStopped());
    }

    public function testConstruct(ListenerProviderInterface $listenerProvider = null): void
    {
        $dispatcher = new Dispatcher($listenerProvider ?? new ListenerProvider());
        self::assertInstanceOf(DispatcherInterface::class, $dispatcher);
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
     * @throws \Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAListenerProvider(): void
    {
        $this->listenerProvider->addSubscriber(TestEventSubscriber::class);
        $this->dispatcher = new Dispatcher($this->listenerProvider);

        $testEvent = new TestEvent();
        self::assertSame('[]', $testEvent->read());

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
         * @var callable(EventInterface<bool>):void $listener
         */
        $listener = static function (
            /*
             * @param EventInterface<bool> $testEvent
             */
            TestEventInterface $testEvent
        ): void {
            $testEvent->write('#BlackLivesMatter');
            $testEvent->stopPropagation();
        };

        $this->listenerProvider->addListener($listener, 1, TestEvent::class, __METHOD__.'Listener');

        $this->dispatcher = new Dispatcher($this->listenerProvider);

        // self::assertEmpty($testEvent->read());
        self::assertSame('[]', $testEvent->read());
        self::assertSame($testEvent, $this->dispatcher->dispatch($testEvent));
        self::assertNotEmpty($testEvent->read());
        self::assertSame('["#BlackLivesMatter"]', $testEvent->read());
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @throws \Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(EventInterface $event): void
    {
        $this->listenerProvider->addListener(static function (EventInterface $event): never {
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

        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }
}
