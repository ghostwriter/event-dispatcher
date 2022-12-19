<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

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
use Ghostwriter\EventDispatcher\Traits\EventTrait;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use RuntimeException;
use Throwable;
use Traversable;
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

    private DispatcherInterface $dispatcher;

    private ListenerProviderInterface $provider;

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
        yield EventInterface::class => [new class() implements EventInterface {
            use EventTrait;
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
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::dispatch
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::isPropagationStopped
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::stopPropagation
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
        self::assertTrue($event->isPropagationStopped());
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::isPropagationStopped
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
    public function testConstruct(?ListenerProviderInterface $listenerProvider = null): void
    {
        $dispatcher = new Dispatcher($listenerProvider);
        self::assertInstanceOf(DispatcherInterface::class, $dispatcher);
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::isPropagationStopped
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::dispatch
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenersForEvent
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
            $this->expectException($throwable::class);
            $this->expectExceptionMessage($throwable->getMessage());
            $this->expectExceptionCode($throwable->getMessage());
        }
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     */
    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        self::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::isPropagationStopped
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::stopPropagation
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::dispatch
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListenerService
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addSubscriber
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addSubscriberService
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getContainer
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getEventType
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenersForEvent
     *
     * @throws Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAListenerProvider(): void
    {
        $this->provider->addSubscriberService(TestEventSubscriber::class);
        $this->dispatcher = new Dispatcher($this->provider);

        $testEvent = new TestEvent();
        self::assertEmpty($testEvent->read());

        $testEventResult = $this->dispatcher->dispatch($testEvent);

        self::assertSame($testEvent, $testEventResult);
        self::assertCount(count($testEvent->read()), $this->provider->getListenersForEvent($testEvent));
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::isPropagationStopped
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::stopPropagation
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::dispatch
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListenerService
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addSubscriber
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addSubscriberService
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getContainer
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getEventType
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenersForEvent
     *
     * @throws Throwable
     */
    public function testMustListeners(): void
    {
        $testEvent = new TestEvent();
        $this->provider->addSubscriberService(TestEventSubscriber::class);
        $this->provider->addListener(
            static function (TestEventInterface $testEvent) {
                $testEvent->write(sprintf('%s', count($testEvent->read())));
                $testEvent->stopPropagation();
            },
            1,
            TestEvent::class,
            __METHOD__ . 'Listener'
        );
        $this->dispatcher = new Dispatcher($this->provider);

        self::assertEmpty($testEvent->read());
        self::assertSame($testEvent, $this->dispatcher->dispatch($testEvent));
        self::assertNotEmpty($testEvent->read());
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\Traits\EventTrait::isPropagationStopped
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::dispatch
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getEventType
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenerId
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenersForEvent
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
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\ErrorEvent::getThrowable
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     *
     * @dataProvider eventDataProvider
     *
     * @throws Throwable
     */
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
