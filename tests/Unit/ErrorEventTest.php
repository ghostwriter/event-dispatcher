<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Closure;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\DispatcherInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\ListenerInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use Ghostwriter\EventDispatcher\Traits\ListenerTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use RuntimeException;
use Throwable;

#[CoversClass(Dispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(Listener::class)]
#[Small]
final class ErrorEventTest extends PHPUnitTestCase
{
    /**
     * @var int
     */
    public const ERROR_CODE = 42;

    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Could not handle the event!';

    private DispatcherInterface $dispatcher;

    private ErrorEvent $errorEvent;

    private ListenerInterface $listener;

    private ListenerProviderInterface $listenerProvider;

    private TestEvent $testEvent;

    private Throwable $throwable;

    protected function setUp(): void
    {
        $this->testEvent = new TestEvent();
        $this->throwable = new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);
        $this->listenerProvider = new ListenerProvider();
        $this->dispatcher = new Dispatcher();
        $this->listener = new Listener(Closure::fromCallable(new TestEventListener()));

        $this->errorEvent = new ErrorEvent($this->testEvent, $this->listener, $this->throwable);
    }

    /**
     * @return iterable<string,array<array-key,class-string>>
     */
    public static function dataProviderImplementsInterface(): iterable
    {
        foreach ([EventInterface::class, ErrorEvent::class] as $interface) {
            yield $interface => [$interface];
        }
    }

    /**
     * @throws \Throwable
     */
    public function testErrorEventComposesEventListenerAndThrowable(): void
    {
        self::assertSame($this->testEvent, $this->errorEvent->getEvent());
        self::assertSame($this->listener, $this->errorEvent->getListener());
        self::assertSame($this->throwable, $this->errorEvent->getThrowable());

        self::assertSame(self::ERROR_MESSAGE, $this->errorEvent->getThrowable()->getMessage());
        self::assertSame(self::ERROR_CODE, $this->errorEvent->getThrowable()->getCode());

        /** @var \Closure(ErrorEvent<bool>):never $errorEvent */
        $errorEventListener = static function (
            /* @var ErrorEvent<bool> $errorEvent */
            ErrorEvent $errorEvent
        ): never {
            // Raise an exception
            throw new \RuntimeException($errorEvent::class);
        };

        $this->listenerProvider->addListener($errorEventListener);

        $this->dispatcher = new Dispatcher($this->listenerProvider);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->expectExceptionCode(self::ERROR_CODE);

        self::assertSame($this->errorEvent, $this->dispatcher->dispatch($this->errorEvent));
        self::assertSame($this->throwable, $this->errorEvent->getThrowable());
        self::assertSame(self::ERROR_MESSAGE, $this->errorEvent->getThrowable()->getMessage());
        self::assertSame(self::ERROR_CODE, $this->errorEvent->getThrowable()->getCode());
    }

    /**
     * @param class-string $interface
     */
    #[DataProvider('dataProviderImplementsInterface')]
    public function testErrorEventImplements(string $interface): void
    {
        self::assertInstanceOf($interface, $this->errorEvent);
    }
}
