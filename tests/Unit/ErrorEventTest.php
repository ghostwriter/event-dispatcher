<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\DispatcherInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\ListenerInterface;
use Ghostwriter\EventDispatcher\Provider;
use Ghostwriter\EventDispatcher\ProviderInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[CoversClass(AbstractEvent::class)]
#[CoversClass(Dispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(Provider::class)]
#[CoversClass(Listener::class)]
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

    private ListenerInterface $listener;

    private ProviderInterface $provider;

    private TestEvent $testEvent;

    private \Throwable $throwable;

    protected function setUp(): void
    {
        $this->testEvent = new TestEvent();
        $this->throwable = new \RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);
        $this->provider = new Provider();
        $this->dispatcher = new Dispatcher();
        $this->listener = Listener::fromInvokableClass(TestEventListener::class);

        $this->errorEvent = new ErrorEvent($this->testEvent, $this->listener, $this->throwable);
    }

    /**
     * @throws \Throwable
     */
    public function testErrorEventComposesEventListenerAndThrowable(): void
    {
        static::assertSame($this->testEvent, $this->errorEvent->getEvent());
        static::assertSame($this->listener, $this->errorEvent->getListener());
        static::assertSame($this->throwable, $this->errorEvent->getThrowable());

        static::assertSame(self::ERROR_MESSAGE, $this->errorEvent->getThrowable()->getMessage());
        static::assertSame(self::ERROR_CODE, $this->errorEvent->getThrowable()->getCode());

        /** @var \Closure(EventInterface<bool>):never */
        $errorEventListener = static function (ErrorEvent $event): never {
            // Raise an exception
            throw new \RuntimeException($event::class);
        };

        $this->provider->listen($errorEventListener);

        $this->dispatcher = new Dispatcher($this->provider);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->expectExceptionCode(self::ERROR_CODE);

        static::assertSame($this->errorEvent, $this->dispatcher->dispatch($this->errorEvent));
        static::assertSame($this->throwable, $this->errorEvent->getThrowable());
        static::assertSame(self::ERROR_MESSAGE, $this->errorEvent->getThrowable()->getMessage());
        static::assertSame(self::ERROR_CODE, $this->errorEvent->getThrowable()->getCode());
    }

    public function testErrorEventImplementsErrorEventInterface(): void
    {
        static::assertInstanceOf(ErrorEventInterface::class, $this->errorEvent);
        static::assertInstanceOf(EventInterface::class, $this->errorEvent);
    }
}
