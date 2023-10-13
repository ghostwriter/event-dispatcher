<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Closure;
use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\ErrorEvent;
use Ghostwriter\EventDispatcher\Interface\DispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\ErrorEventListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use RuntimeException;
use Throwable;

#[CoversClass(AbstractEvent::class)]
#[CoversClass(Dispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(ListenerProvider::class)]
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

    private ErrorEventInterface $errorEvent;

    private Closure $listener;

    private ListenerProviderInterface $provider;

    private TestEvent $testEvent;

    private Throwable $throwable;

    protected function setUp(): void
    {
        $this->testEvent = new TestEvent();
        $this->throwable = new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);
        $this->provider = new ListenerProvider();
        $this->dispatcher = new Dispatcher();
        $this->listener = ([new TestEventListener(), '__invoke'])(...);

        $this->errorEvent = new ErrorEvent($this->testEvent, $this->listener, $this->throwable);
    }

    /**
     * @throws Throwable
     */
    public function testErrorEventComposesEventListenerAndThrowable(): void
    {
        self::assertSame($this->testEvent, $this->errorEvent->getEvent());
        self::assertSame($this->listener, $this->errorEvent->getListener());
        self::assertSame($this->throwable, $this->errorEvent->getThrowable());

        self::assertSame(self::ERROR_MESSAGE, $this->errorEvent->getThrowable()->getMessage());
        self::assertSame(self::ERROR_CODE, $this->errorEvent->getThrowable()->getCode());

        $this->provider->listen(ErrorEventListener::class);

        $this->dispatcher = new Dispatcher($this->provider);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->expectExceptionCode(self::ERROR_CODE);

        $this->dispatcher->dispatch($this->errorEvent);
    }

    public function testErrorEventImplementsErrorEventInterface(): void
    {
        self::assertInstanceOf(ErrorEventInterface::class, $this->errorEvent);
        self::assertInstanceOf(EventInterface::class, $this->errorEvent);
    }
}
