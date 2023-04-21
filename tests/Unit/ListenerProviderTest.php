<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Generator;
use Ghostwriter\EventDispatcher\Contract\EventDispatcherExceptionInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use Ghostwriter\EventDispatcher\Traits\ListenerTrait;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[CoversClass(ListenerProvider::class)]
#[CoversClass(ListenerTrait::class)]
#[Small]
final class ListenerProviderTest extends PHPUnitTestCase
{
    /**
     * @var int
     */
    private const PRIORITY = 0;

    public ListenerProviderInterface $provider;

    protected function setUp(): void
    {
        $this->provider = new ListenerProvider();
    }

    /**
     * @return iterable<string,array{0:array{0:object|string,1:string}|callable,1?:int,2?:string}>
     */
    public static function supportedListenersDataProvider(): Iterator
    {
        yield 'AnonymousFunctionListenerMissingClosureParamType' => [
            static fn (EventInterface $event): mixed => self::assertSame(TestEvent::class, $event::class),
            self::PRIORITY,
            TestEvent::class,
        ];

        yield 'AnonymousFunctionListener' => [
            static function (TestEvent $testEvent): void {
                $testEvent->write($testEvent::class);
            },
        ];

        yield 'FunctionListener' => ['Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction'];

        yield 'StaticMethodListener' => [TestEventListener::class . '::onStatic'];

        yield 'CallableArrayStaticMethodListener' => [[TestEventListener::class, 'onStaticCallableArray']];

        yield 'CallableArrayInstanceListener' => [[new TestEventListener(), 'onTest']];

        yield 'InvokableListener' => [new TestEventListener()];
    }

    public function testListenRaisesExceptionIfUnableToDetermineEventType(): void
    {
        /** @psalm-suppress MissingClosureParamType */
        $listener = static function ($testEvent): void {
            if ($testEvent instanceof TestEvent) {
                $testEvent->write($testEvent::class);
            }
        };

        $this->expectException(EventDispatcherExceptionInterface::class);
        $this->expectExceptionMessage('Missing type declarations for "$testEvent" parameter.');
        $this->provider->addListener($listener);
    }

    /**
     * @param array{0:object|string,1:string}|callable $listener
     */
    #[DataProvider('supportedListenersDataProvider')]
    public function testProviderDetectsEventType(
        array|callable $listener,
        int $priority = 0,
        ?string $event = null
    ): void {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->provider);

        /** @var callable(object):void $listener */
        $listenerId = $this->provider->addListener($listener, $priority, $event);

        /** @var Generator<ListenerInterface> $listeners */
        $listeners = $this->provider->getListenersForEvent(new TestEvent());

        self::assertSame($listener, $listeners->current()->getListener());

        $this->provider->removeListener($listenerId);

        self::assertCount(0, iterator_to_array($this->provider->getListenersForEvent(new TestEvent())));
    }
}
