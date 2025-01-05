<?php

declare(strict_types=1);

namespace Tests\Unit;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventInterface;
use Tests\Fixture\TestEventListener;
use Tests\Fixture\TestListener;
use Throwable;

use function iterator_to_array;

abstract class AbstractTestCase extends TestCase
{
    public const int ERROR_CODE = 42;

    public const string ERROR_MESSAGE = 'Could not handle the event!';

    protected ErrorEventInterface $errorEvent;

    protected EventDispatcherInterface $eventDispatcher;

    protected string $listener;

    protected ListenerProviderInterface $listenerProvider;

    protected TestEventInterface $testEvent;

    protected Throwable $throwable;

    /**
     * @throws Throwable
     */
    #[Override]
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

    #[Override]
    final protected function tearDown(): void
    {
        parent::tearDown();

        Container::getInstance()->__destruct();
    }

    /**
     * @template Event of object
     *
     * @param Event $event
     *
     * @throws Throwable
     */
    final public function assertListenersCount(int $expectedCount, object $event): void
    {
        self::assertCount($expectedCount, iterator_to_array($this->listenerProvider->listeners($event)));
    }

    /**
     * @template Event of object
     *
     * @param Event $event
     *
     * @throws ExceptionInterface
     * @throws Throwable
     */
    final public function dispatch(object $event): void
    {
        self::assertSame($event, $this->eventDispatcher->dispatch($event));
    }

    /**
     * @return Generator<string,array{0:object}>
     */
    public static function eventDataProvider(): Generator
    {
        $testEvent = new TestEvent();

        yield from [
            stdClass::class => [new stdClass()],
            'noop' => [new class() {}],
            TestEvent::class => [$testEvent],
            ErrorEvent::class => [
                new ErrorEvent(
                    $testEvent,
                    TestListener::class,
                    new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE)
                ),
            ],
        ];
    }
}
