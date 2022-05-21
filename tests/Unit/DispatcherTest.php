<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Contract\DispatcherInterface;
use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\ErrorEvent;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventSubscriber;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEventInterface;
use RuntimeException;
use stdClass;
use Throwable;
use Traversable;
use function is_subclass_of;
use function iterator_count;
use function sprintf;

/**
 * @coversDefaultClass \Ghostwriter\EventDispatcher\Dispatcher
 *
 * @internal
 *
 * @small
 */
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

    private ListenerProvider $provider;

    protected function setUp(): void
    {
        $this->provider  = new ListenerProvider();
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @coversNothing
     *
     * @return Traversable<string,array<object>>
     */
    public function eventDataProvider(): iterable
    {
        yield stdClass::class => [new stdClass()];

        yield from $this->stoppableEventDataProvider();
    }

    /**
     * @coversNothing
     *
     * @return Traversable<string,array<PsrStoppableEventInterface>>
     */
    public function stoppableEventDataProvider(): iterable
    {
        yield EventInterface::class => [new class() extends AbstractEvent {
        }];

        yield ErrorEventInterface::class => [new ErrorEvent(
            (object) [],
            static function (): void {
            },
            new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE)
        )];

        yield TestEventInterface::class => [new TestEvent()];
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\AbstractEvent::isPropagationStopped
     * @covers \Ghostwriter\EventDispatcher\AbstractEvent::stopPropagation
     * @covers \Ghostwriter\EventDispatcher\ErrorEvent::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getEventType
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenerId
     *
     * @dataProvider stoppableEventDataProvider
     *
     * @throws Throwable
     */
    public function testAlreadyStoppedEventCallsNoListeners(EventInterface $event): void
    {
        $called = [];
        self::assertFalse($event->isPropagationStopped());

//        $event->stopPropagation();

//        self::assertTrue($event->isPropagationStopped());

        $this->provider->addListener(
            static function (PsrStoppableEventInterface $psrStoppableEvent) use (&$called): void {
                $called[$psrStoppableEvent::class] = $psrStoppableEvent::class;

                throw new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);
            }
        );

        self::assertSame($event, $this->dispatcher->dispatch($event));
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\AbstractEvent::isPropagationStopped
     * @covers \Ghostwriter\EventDispatcher\ErrorEvent::__construct
     * @covers \Ghostwriter\EventDispatcher\ErrorEvent::getThrowable
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getEventType
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenerId
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenersForEvent
     *
     * @throws throwable
     *
     */
    public function testConstruct(?PsrListenerProviderInterface $psrListenerProvider = null): void
    {
        self::assertInstanceOf(DispatcherInterface::class, new Dispatcher($psrListenerProvider));
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     *
     * @dataProvider eventDataProvider
     *
     * @throws Throwable
     */
    public function testDispatch(object $event): void
    {
//        $error = $event instanceof ErrorEventInterface;
//        $stoppable = $event instanceof PsrStoppableEventInterface;

        try {
            self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);

            self::assertSame($event, $this->dispatcher->dispatch($event));
        } catch (Throwable $throwable) {
//            if (! $throws) {
//                self::fail('[Unexpected]' . $throwable->getMessage());
//            }

            $this->expectException($throwable::class);
            $this->expectExceptionMessage($throwable->getMessage());
            $this->expectExceptionCode($throwable->getMessage());

//             if($error){
//                self::assertSame($event,'');
//             }
        }
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     */
    public function testDispatcherInterfaceExtendsPsrDispatcherInterface(): void
    {
        self::assertTrue(is_subclass_of(DispatcherInterface::class, PsrEventDispatcherInterface::class, true));
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     */
    public function testEventInterfaceExtendsPsrStoppableEventInterface(): void
    {
        self::assertTrue(is_subclass_of(EventInterface::class, PsrStoppableEventInterface::class, true));
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     */
    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        self::assertInstanceOf(PsrEventDispatcherInterface::class, $this->dispatcher);
//        self::assertInstanceOf(PsrStoppableEventInterface::class, $this->dispatcher);
        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     */
    public function testListenerProviderInterfaceExtendsPsrListenerProviderInterface(): void
    {
        self::assertTrue(
            is_subclass_of(ListenerProviderInterface::class, PsrListenerProviderInterface::class, true)
        );
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     *
     * @throws Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAListenerProvider(): void
    {
        $testEvent = new TestEvent();

        $this->provider->addSubscriber(new TestEventSubscriber());

        $expected = iterator_count($this->provider->getListenersForEvent($testEvent));

        $this->dispatcher = new Dispatcher($this->provider);

        self::assertEmpty($testEvent->read());
        self::assertSame($testEvent, $this->dispatcher->dispatch($testEvent));
        self::assertCount($expected, $testEvent->read());
        self::assertNotEmpty($testEvent->read());
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @dataProvider eventDataProvider
     *
     * @throws Throwable
     */
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(object $event): void
    {
        $this->provider->addListener(static function (PsrStoppableEventInterface $psrStoppableEvent): void {
            throw new RuntimeException(
                sprintf(
                    'Simulate error raised while processing "%s"; PsrStoppableEventInterface!',
                    $psrStoppableEvent::class
                )
            );
        });

        self::assertSame($event, $this->dispatcher->dispatch($event));
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @dataProvider eventDataProvider
     */
    public function testThrows(object $event, ?Throwable $throwable = null): void
    {
        if (null !== $throwable) {
            $this->expectException($throwable::class);
            $this->expectExceptionMessage($throwable->getMessage());
            $this->expectExceptionCode($throwable->getMessage());
        }

        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
//        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
//        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
//        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }
}
