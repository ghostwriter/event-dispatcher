<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Traversable;
use function iterator_to_array;

/**
 * @coversDefaultClass \Ghostwriter\EventDispatcher\ListenerProvider
 *
 * @internal
 *
 * @small
 */
final class ListenerProviderTest extends PHPUnitTestCase
{
    public ListenerProviderInterface $provider;

    protected function setUp(): void
    {
        $this->provider = new ListenerProvider();
    }

    /**
     * @coversNothing
     *
     * @return Traversable<string,array<callable|int|string>>
     */
    public function supportedListenersDataProvider(): Traversable
    {
        $priority = 0;

        yield 'AnonymousFunctionListenerMissingClosureParamType' => [
            /** @psalm-suppress MissingClosureParamType */
            static fn ($event) => self::assertIsObject($event),
            $priority,
            TestEvent::class,
        ];

        yield 'AnonymousFunctionListener' => [
            static function (TestEvent $event): void {
                $event->write($event::class);
            },
        ];

        yield 'FunctionListener' => ['Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction'];

        yield 'StaticMethodListener' => [TestEventListener::class . '::onStatic'];

        yield 'CallableArrayStaticMethodListener' => [[TestEventListener::class, 'onStaticCallableArray']];

        yield 'CallableArrayInstanceListener' => [[new TestEventListener(), 'onTest']];

        yield 'InvokableListener' => [new TestEventListener()];
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     */
    public function testListenRaisesExceptionIfUnableToDetermineEventType(): void
    {
        /** @psalm-suppress MissingClosureParamType */
        $listener = static function ($testEvent): void {
            if ($testEvent instanceof TestEvent) {
                $testEvent->write($testEvent::class);
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing type declarations for "$testEvent" parameter.');
        $this->provider->addListener($listener);
    }

    /**
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::__construct
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::addListener
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::getListenersForEvent
     * @covers \Ghostwriter\EventDispatcher\ListenerProvider::removeListener
     *
     * @param callable(EventInterface):void $listener
     *
     * @dataProvider supportedListenersDataProvider
     */
    public function testProviderDetectsEventType(
        callable $listener,
        int $priority = 0,
        ?string $event = null
    ): void {
        $listenerId = $this->provider->addListener($listener, $priority, $event);

        self::assertSame([$listener], iterator_to_array($this->provider->getListenersForEvent(new TestEvent())));

        $this->provider->removeListener($listenerId);
    }
}
