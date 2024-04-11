<?php

declare(strict_types=1);

namespace Tests\Unit;

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
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventInterface;
use Tests\Fixture\TestEventListener;
use Tests\Fixture\TestListener;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

use function iterator_to_array;

abstract class AbstractTestCase extends TestCase
{
    public const int ERROR_CODE = 42;

    public const string ERROR_MESSAGE = 'Could not handle the event!';

    protected ErrorEventInterface $errorEvent;

    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @template TEvent of object
     * @template TListener of object
     *
     * @var class-string<(callable(TEvent):void)&TListener>
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

        $this->listenerProvider = ListenerProvider::new();
        $this->eventDispatcher = EventDispatcher::new($this->listenerProvider);

        $this->testEvent = new TestEvent();
        $this->listener = TestEventListener::class;
        $this->throwable = new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);
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
    final public function dispatch(EventInterface $event): EventInterface
    {
        self::assertSame($event, $this->eventDispatcher->dispatch($event));

        return $event;
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    final public function listen(string $event, string ...$listeners): self
    {
        foreach ($listeners as $listener) {
            $this->listenerProvider->listen($event, $listener);
        }

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

        return $this;
    }

    /**
     * @return Generator<string,list<EventInterface<bool>>>
     */
    public static function eventDataProvider(): Generator
    {
        yield EventInterface::class => [new class () implements EventInterface {
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
