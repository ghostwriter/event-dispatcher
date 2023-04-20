<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\Contract\DispatcherInterface;
use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\ErrorEvent;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventSubscriber;
use Ghostwriter\EventDispatcher\Traits\EventTrait;
use Ghostwriter\EventDispatcher\Traits\ListenerTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use RuntimeException;
use Throwable;
use Traversable;
use function sprintf;

#[CoversClass(Dispatcher::class)]
#[Small]
#[UsesClass(ErrorEvent::class)]
#[UsesClass(EventTrait::class)]
#[UsesClass(ListenerProvider::class)]
#[UsesClass(ListenerTrait::class)]
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
        $this->listenerProvider  = new ListenerProvider();
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @return Traversable<string,list<EventInterface>>
     */
    public static function eventDataProvider(): Traversable
    {
        yield EventInterface::class => [new class() implements EventInterface {
            use EventTrait;
        }];

        yield ErrorEventInterface::class => [new ErrorEvent(
            new TestEvent(),
            (new class(static fn (): mixed => null) implements ListenerInterface {
                use ListenerTrait;
            }),
            new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE)
        )];

        yield TestEventInterface::class => [new TestEvent()];
    }

    #[DataProvider('eventDataProvider')]
    public function testAlreadyStoppedEventCallsNoListeners(EventInterface $event): void
    {
        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        self::assertTrue($event->isPropagationStopped());

        $this->listenerProvider->addListener(
            static function (EventInterface $event): never {
                throw new RuntimeException(self::ERROR_MESSAGE . $event::class, self::ERROR_CODE);
            }
        );

        self::assertSame($event, $this->dispatcher->dispatch($event));
        self::assertTrue($event->isPropagationStopped());
    }

    public function testConstruct(?ListenerProviderInterface $listenerProvider = null): void
    {
        $dispatcher = new Dispatcher($listenerProvider ?? new ListenerProvider());
        self::assertInstanceOf(DispatcherInterface::class, $dispatcher);
    }

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
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAListenerProvider(): void
    {
        $this->listenerProvider->addSubscriber(TestEventSubscriber::class);
        $this->dispatcher = new Dispatcher($this->listenerProvider);

        $testEvent = new TestEvent();
        self::assertEmpty($testEvent->read());

        $testEventResult = $this->dispatcher->dispatch($testEvent);

        self::assertSame($testEvent, $testEventResult);
        self::assertCount(
            is_countable($testEvent->read()) ? count($testEvent->read()) : 0,
            iterator_to_array($this->listenerProvider->getListenersForEvent($testEvent))
        );
    }

    /**
     * @throws Throwable
     */
    public function testMustListeners(): void
    {
        $testEvent = new TestEvent();
        $this->listenerProvider->addSubscriber(TestEventSubscriber::class);
        $this->listenerProvider->addListener(
            static function (TestEventInterface $testEvent): void {
                $testEvent->write(sprintf('%s', is_countable($testEvent->read()) ? count($testEvent->read()) : 0));
                $testEvent->stopPropagation();
            },
            1,
            TestEvent::class,
            __METHOD__ . 'Listener'
        );
        $this->dispatcher = new Dispatcher($this->listenerProvider);

        self::assertEmpty($testEvent->read());
        self::assertSame($testEvent, $this->dispatcher->dispatch($testEvent));
        self::assertNotEmpty($testEvent->read());
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(EventInterface $event): void
    {
        $this->listenerProvider->addListener(static function (EventInterface $event): never {
            throw new RuntimeException(
                sprintf('Simulate error raised while processing "%s"; PsrStoppableEventInterface!', $event::class)
            );
        });

        self::assertSame($event, $this->dispatcher->dispatch($event));
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testThrows(object $event): void
    {
        if ($event instanceof ErrorEventInterface) {
            $throwable = $event->getThrowable();

            $this->expectException($throwable::class);
            $this->expectExceptionMessage($throwable->getMessage());
            $this->expectExceptionCode($throwable->getCode());

            throw $throwable;
        }

        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }
}
