<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Generator;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineTypeDeclarationsException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @coversDefaultClass \Ghostwriter\EventDispatcher\ListenerProvider
 *
 * @internal
 *
 * @small
 */
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
     * @coversNothing
     *
     * @return iterable<string,array{0:array{0:object|string,1:string}|callable,1?:int,2?:string}>
     */
    public function supportedListenersDataProvider(): iterable
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

    /**
     * @covers \Ghostwriter\EventDispatcher\Exception\FailedToDetermineTypeDeclarationsException::missingTypeDeclarations
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getEventType
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenerId
     */
    public function testListenRaisesExceptionIfUnableToDetermineEventType(): void
    {
        /** @psalm-suppress MissingClosureParamType */
        $listener = static function ($testEvent): void {
            if ($testEvent instanceof TestEvent) {
                $testEvent->write($testEvent::class);
            }
        };

        $this->expectException(FailedToDetermineTypeDeclarationsException::class);
        $this->expectExceptionMessage('Missing type declarations for "$testEvent" parameter.');
        $this->provider->addListener($listener);
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getEventType
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenerId
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenersForEvent
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::removeListener
     * @covers \Ghostwriter\EventDispatcher\Traits\ListenerTrait::__construct
     * @covers \Ghostwriter\EventDispatcher\Traits\ListenerTrait::getListener
     *
     * @dataProvider supportedListenersDataProvider
     *
     * @param array{0:object|string,1:string}|callable $listener
     */
    public function testProviderDetectsEventType(
        callable $listener,
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

        self::assertCount(0, $this->provider->getListenersForEvent(new TestEvent()));
    }
}
