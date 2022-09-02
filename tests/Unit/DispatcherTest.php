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
use RuntimeException;
use Throwable;
use Traversable;
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
     * @return Traversable<string,list<EventInterface>>
     */
    public function eventDataProvider(): Traversable
    {
        yield EventInterface::class => [new class() extends AbstractEvent {
        }];

        yield ErrorEventInterface::class => [new ErrorEvent(
            new TestEvent(),
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
     * @dataProvider eventDataProvider
     *
     * @throws Throwable
     */
    public function testAlreadyStoppedEventCallsNoListeners(EventInterface $event): void
    {
        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        self::assertTrue($event->isPropagationStopped());

        $this->provider->addListener(
            static function (EventInterface $event): void {
                throw new RuntimeException(self::ERROR_MESSAGE . $event::class, self::ERROR_CODE);
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
    public function testConstruct(?ListenerProviderInterface $psrListenerProvider = null): void
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
    public function testDispatch(EventInterface $event): void
    {
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
//

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     */
    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     *
     * @throws Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAListenerProvider(): void
    {
        $testEvent = new TestEvent();

        $this->provider->addSubscriberService(TestEventSubscriber::class);

        $expected = iterator_count($this->provider->getListenersForEvent($testEvent));

        $this->dispatcher = new Dispatcher($this->provider);

        self::assertEmpty($testEvent->read());
        self::assertSame($testEvent, $this->dispatcher->dispatch($testEvent));
        self::assertCount($expected, $testEvent->read());
        self::assertNotEmpty($testEvent->read());
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     *
     * @dataProvider eventDataProvider
     *
     * @throws Throwable
     */
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(EventInterface $event): void
    {
        $this->provider->addListener(static function (EventInterface $event): void {
            throw new RuntimeException(
                sprintf('Simulate error raised while processing "%s"; PsrStoppableEventInterface!', $event::class)
            );
        });

        self::assertSame($event, $this->dispatcher->dispatch($event));
    }

    /**
     * @covers       \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     *
     * @dataProvider eventDataProvider
     *
     * @throws Throwable
     */
    public function testThrows(EventInterface $event): void
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
