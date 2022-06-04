<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\ErrorEvent;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use RuntimeException;
use Throwable;
use Traversable;

/**
 * @coversDefaultClass \Ghostwriter\EventDispatcher\ErrorEvent
 *
 * @internal
 *
 * @small
 */
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

    private Dispatcher $dispatcher;

    private ErrorEvent $error;

    private TestEvent $event;

    /**
     * @var callable(EventInterface):void
     */
    private $listener;

    private ListenerProvider $provider;

    private Throwable $throwable;

    protected function setUp(): void
    {
        $this->event     = new TestEvent();
        $this->listener  = new TestEventListener();
        $this->provider  = new ListenerProvider();
        $this->throwable = new RuntimeException(self::ERROR_MESSAGE, self::ERROR_CODE);
        $this->dispatcher = new Dispatcher();
        $this->error = new ErrorEvent($this->event, $this->listener, $this->throwable);
    }

    /**
     * @coversNothing
     *
     * @return Traversable<string,list<class-string<EventInterface>>>
     */
    public function dataProviderImplementsInterface(): Traversable
    {
        foreach ([EventInterface::class, ErrorEventInterface::class] as $interface) {
            yield $interface => [$interface];
        }
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\AbstractEvent::isPropagationStopped
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::__construct
     * @covers \Ghostwriter\EventDispatcher\Dispatcher::dispatch
     * @covers \Ghostwriter\EventDispatcher\ErrorEvent::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getEventType
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenerId
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenersForEvent
     *
     * @throws Throwable
     */
    public function testErrorEventComposesEventListenerAndThrowable(): void
    {
        self::assertSame($this->event, $this->error->getEvent());
        self::assertSame($this->listener, $this->error->getListener());
        self::assertSame($this->throwable, $this->error->getThrowable());

        self::assertSame(self::ERROR_MESSAGE, $this->error->getThrowable()->getMessage());
        self::assertSame(self::ERROR_CODE, $this->error->getThrowable()->getCode());

        $this->provider->addListener(static function (ErrorEvent $errorEvent): void {
            // Raise an exception
            throw new RuntimeException($errorEvent::class);
        });

        $this->dispatcher = new Dispatcher($this->provider);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->expectExceptionCode(self::ERROR_CODE);

        self::assertSame($this->error, $this->dispatcher->dispatch($this->error));
        self::assertSame($this->throwable, $this->error->getThrowable());
        self::assertSame(self::ERROR_MESSAGE, $this->error->getThrowable()->getMessage());
        self::assertSame(self::ERROR_CODE, $this->error->getThrowable()->getCode());
    }

    /**
     * @coversNothing
     * @dataProvider dataProviderImplementsInterface
     */
    public function testErrorEventImplements(string $interface): void
    {
        self::assertInstanceOf($interface, $this->error);
    }
}
