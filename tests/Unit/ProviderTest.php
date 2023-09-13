<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\ExceptionInterface;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\Provider;
use Ghostwriter\EventDispatcher\ProviderInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent2;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Provider::class)]
#[CoversClass(Listener::class)]
#[Small]
final class ProviderTest extends TestCase
{
    /**
     * @var int
     */
    private const PRIORITY = 0;

    public ProviderInterface $provider;

    protected function setUp(): void
    {
        $this->provider = new Provider();
    }

    /**
     * @return \Generator<string,list{0:callable|callable-string|\Closure,1?:0,2?:string}>
     */
    public static function supportedListenersDataProvider(): iterable
    {
        yield from [
            'AnonymousFunctionListenerMissingClosureParamType' => [
                static fn (EventInterface $event) => \assert(TestEvent::class === $event::class),
                self::PRIORITY,
                TestEvent::class,
            ],
            'AnonymousFunctionListener' => [
                static function (TestEvent $testEvent): void {
                    $testEvent->write($testEvent::class);
                },
            ],
            'FunctionListener' => ['Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction'],
            'StaticMethodListener' => [TestEventListener::class . '::onStatic'],
            'CallableArrayStaticMethodListener' => [[TestEventListener::class, 'onStaticCallableArray']],
            'CallableArrayInstanceListener' => [[new TestEventListener(), 'onTest']],
            'InvokableListener' => [new TestEventListener()],
        ];
    }

    public function testListenRaisesExceptionIfUnableToDetermineEventType(): void
    {
        /**
         * @param object $testEvent
         *
         * @psalm-suppress MissingClosureParamType
         *
         * @var \Closure(object):void $listener
         */
        $listener = static function ($testEvent): void {
            if ($testEvent instanceof TestEvent) {
                $testEvent->write($testEvent::class);
            }
        };

        $this->expectException(ExceptionInterface::class);
        $this->expectException(MissingParameterTypeDeclarationException::class);
        $this->expectExceptionMessage('testEvent');
        $this->provider->listen($listener);
    }

    public function testProviderBind(): void
    {
        $testEvent = new TestEvent();

        static::assertSame('', $testEvent->read());

        static::assertInstanceOf(ProviderInterface::class, $this->provider);

        $listenerId = $this->provider->bind(TestEvent::class, TestEventListener::class);

        static::assertSame(TestEventListener::class, $listenerId);

        $listeners = $this->provider->listeners($testEvent);

        foreach ($listeners as $listener) {
            $listener($testEvent);
        }

        static::assertSame(TestEventListener::class . '::__invoke', $testEvent->read());

        $this->provider->remove($listenerId);

        static::assertCount(0, iterator_to_array($this->provider->listeners($testEvent)));
    }

    /**
     * @param callable(object):void             $listener
     * @param class-string<EventInterface>|null $event
     */
    #[DataProvider('supportedListenersDataProvider')]
    public function testProviderDetectsEventType(
        callable $listener,
        int $priority = 0,
        string $event = null
    ): void {
        static::assertInstanceOf(ProviderInterface::class, $this->provider);

        $listenerId = $this->provider->listen($listener, $priority, $event);

        $listeners = $this->provider->listeners(new TestEvent());

        static::assertInstanceOf(Listener::class, $listeners->current());

        $this->provider->remove($listenerId);

        static::assertCount(0, iterator_to_array($this->provider->listeners(new TestEvent())));
    }

    public function testProviderDetectsIntersectionTypes(): void
    {
        $testListener = new TestListener();

        /**
         * @var callable(EventInterface):void $listener
         */
        $listener = [$testListener, 'intersection'];

        $this->provider->listen($listener);

        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $listeners = $this->provider->listeners($event);

            static::assertInstanceOf(Listener::class, $listeners->current());

            static::assertCount(1, iterator_to_array($listeners));
        }
    }

    public function testProviderDetectsUnionTypes(): void
    {
        $testListener = new TestListener();

        /**
         * @var callable(EventInterface):void $listener
         */
        $listener = [$testListener, 'union'];

        $this->provider->listen($listener);

        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $listeners = $this->provider->listeners($event);

            static::assertInstanceOf(Listener::class, $listeners->current());

            static::assertCount(1, iterator_to_array($listeners));
        }
    }

    public function testProviderImplementsProviderInterface(): void
    {
        static::assertInstanceOf(ProviderInterface::class, $this->provider);
    }
}
