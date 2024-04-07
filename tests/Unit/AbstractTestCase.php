<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Ghostwriter\EventDispatcherTests\Fixture\TestEvent;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventInterface;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventListener;
use Ghostwriter\EventDispatcherTests\Fixture\TestListener;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

use function iterator_to_array;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var int
     */
    public const ERROR_CODE = 42;

    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Could not handle the event!';

    protected ErrorEventInterface $errorEvent;

    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @var class-string<(callable(EventInterface<bool>):void)&object>
     */
    protected string $listener;

    protected ListenerProviderInterface $listenerProvider;

    protected TestEventInterface $testEvent;

    protected Throwable $throwable;

    /**
     * @throws Throwable
     */
    final protected function setUp(): void
    {
        parent::setUp();

        $this->throwable = new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);
        $this->listenerProvider = new ListenerProvider();
        $this->eventDispatcher = EventDispatcher::new($this->listenerProvider);
        $this->listener = TestEventListener::class;
        $this->testEvent = new TestEvent();
        $this->errorEvent = new ErrorEvent($this->testEvent, $this->listener, $this->throwable);
    }

    final protected function tearDown(): void
    {
        parent::tearDown();

        Container::getInstance()->__destruct();
    }

    final public function assertListenersCount(int $expectedCount, EventInterface $event): void
    {
        self::assertCount($expectedCount, iterator_to_array($this->listenerProvider->getListenersForEvent($event)));
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    final public function bind(string $event, string ...$listeners): self
    {
        foreach ($listeners as $listener) {
            $this->listenerProvider->bind($event, $listener);
        }

        $this->eventDispatcher = EventDispatcher::new($this->listenerProvider);

        return $this;
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    final public function dispatch(EventInterface $event): EventInterface
    {
        self::assertSame($event, $this->eventDispatcher->dispatch($event));

        return $event;
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    final public function listen(string ...$listeners): self
    {
        foreach ($listeners as $listener) {
            $this->listenerProvider->listen($listener);
        }

        //        $this->dispatcher = new EventDispatcher($this->listenerProvider);

        return $this;
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    final public function subscribe(string ...$subscribers): self
    {
        foreach ($subscribers as $subscriber) {
            $this->listenerProvider->subscribe($subscriber);
        }

        //        $this->dispatcher = new EventDispatcher($this->listenerProvider);

        return $this;
    }

    /**
     * @return Generator<string,list<EventInterface<bool>>>
     */
    public static function eventDataProvider(): Generator
    {
        yield EventInterface::class => [new class() implements EventInterface {
            use EventTrait;
        }, ];

        $testEvent = new TestEvent();

        yield TestEventInterface::class => [$testEvent];

        yield ErrorEvent::class => [
            new ErrorEvent(
                $testEvent,
                TestListener::class,
                new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE)
            ),
        ];
    }
}
