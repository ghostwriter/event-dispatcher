<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\DispatcherInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\Provider;
use Ghostwriter\EventDispatcher\ProviderInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractEvent::class)]
#[CoversClass(Dispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(Listener::class)]
#[CoversClass(Provider::class)]
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

    private ProviderInterface $provider;

    protected function setUp(): void
    {
        $this->provider = new Provider();
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @return \Generator<string,list<EventInterface>>
     */
    public static function eventDataProvider(): \Generator
    {
        yield EventInterface::class => [
            new /** @extends AbstractEvent<bool> */ class() extends AbstractEvent {},
        ];

        yield ErrorEvent::class => [
            new ErrorEvent(
                new TestEvent(),
                new Listener(static fn (): mixed => null),
                new \RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE)
            ),
        ];

        yield TestEventInterface::class => [new TestEvent()];
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @throws \Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testAlreadyStoppedEventCallsNoListeners(EventInterface $event): void
    {
        static::assertFalse($event->isStopped());

        $event->stop();

        static::assertTrue($event->isStopped());

        $provider = new Provider();

        $provider->listen(static function (EventInterface $event): never {
            throw new \RuntimeException(self::ERROR_MESSAGE . $event::class, self::ERROR_CODE);
        });

        $dispatcher = new Dispatcher($provider);

        static::assertSame($event, $dispatcher->dispatch($event));
        static::assertTrue($event->isStopped());
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            DispatcherInterface::class,
            new Dispatcher(
                new Provider(
                    Container::getInstance()
                )
            )
        );
    }

    /**
     * @param EventInterface<bool> $event
     */
    #[DataProvider('eventDataProvider')]
    public function testDispatch(EventInterface $event): void
    {
        static::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
        static::assertSame($event, $this->dispatcher->dispatch($event));
    }

    public function testImplementsDispatcherInterfaceAndPsrEventDispatcherInterface(): void
    {
        static::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }

    /**
     * @throws \Throwable
     */
    public function testMustCallListenersSynchronouslyInTheOrderTheyAreReturnedFromAProvider(): void
    {
        $this->provider->subscribe(TestEventSubscriber::class);
        $this->dispatcher = new Dispatcher($this->provider);

        $testEvent = new TestEvent();
        static::assertSame('', $testEvent->read());

        $testEventResult = $this->dispatcher->dispatch($testEvent);

        static::assertSame($testEvent, $testEventResult);
        static::assertCount(
            $testEvent->count(),
            iterator_to_array($this->provider->listeners($testEvent))
        );
    }

    /**
     * @throws \Throwable
     */
    public function testMustListeners(): void
    {
        $testEvent = new TestEvent();

        $this->provider->subscribe(TestEventSubscriber::class);

        /**
         * @var \Closure(EventInterface<bool>):void $listener
         *
         * @param EventInterface<bool> $testEvent
         */
        $listener = static function (TestEventInterface $testEvent): void {
            $testEvent->write('#BlackLivesMatter');

            $testEvent->stop();
        };

        $this->provider->listen($listener, 1, TestEvent::class, __METHOD__ . 'Listener');

        $this->dispatcher = new Dispatcher($this->provider);

        static::assertSame('', $testEvent->read());

        static::assertSame($testEvent, $this->dispatcher->dispatch($testEvent));

        static::assertNotEmpty($testEvent->read());

        static::assertSame('#BlackLivesMatter', $testEvent->read());
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @throws \Throwable
     */
    #[DataProvider('eventDataProvider')]
    public function testReturnsEventWithoutResolvingListenersIfPropagationIsStopped(EventInterface $event): void
    {
        $this->provider->listen(static function (EventInterface $event): never {
            throw new \RuntimeException(sprintf('Simulate error raised while processing "%s"; PsrStoppableEventInterface!', $event::class));
        });

        static::assertSame($event, $this->dispatcher->dispatch($event));
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

        static::assertInstanceOf(DispatcherInterface::class, $this->dispatcher);
    }
}
