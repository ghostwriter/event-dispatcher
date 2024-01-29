<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\IntersectionParameterTypeDeclarationListener;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\UnionParameterTypeDeclarationListener;
use Ghostwriter\EventDispatcherTests\Fixture\TestEvent;
use Ghostwriter\EventDispatcherTests\Fixture\TestEvent2;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

use function iterator_to_array;

#[CoversClass(AbstractEvent::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
final class ListenerProviderTest extends AbstractTestCase
{
    /**
     * @var int
     */
    private const PRIORITY = 0;

    /**
     * @throws Throwable
     */
    public function testProviderBind(): void
    {
        $testEvent = new TestEvent();

        self::assertSame('', $testEvent->read());

        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);

        $this->listenerProvider->bind(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, $testEvent);

        $container = Container::getInstance();

        $listeners = $this->listenerProvider->getListenersForEvent($testEvent);
        foreach ($listeners as $listener) {
            $container->invoke($listener, [$testEvent]);
        }

        self::assertSame(TestEventListener::class . '::__invoke', $testEvent->read());

        $this->listenerProvider->remove(TestEventListener::class);

        $this->assertListenersCount(0, $testEvent);
    }

    /**
     * @throws Throwable
     */
    public function testProviderDetectsEventType(): void
    {
        $testEvent = new TestEvent();

        $this->assertListenersCount(0, $testEvent);

        $this->listenerProvider->listen(TestEventListener::class);

        $this->assertListenersCount(1, $testEvent);

        $this->listenerProvider->remove(TestEventListener::class);

        $this->assertListenersCount(0, $testEvent);
    }

    public function testProviderDetectsIntersectionTypes(): void
    {
        $this->listenerProvider->listen(IntersectionParameterTypeDeclarationListener::class);

        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $listeners = $this->listenerProvider->getListenersForEvent($event);
            self::assertCount(1, iterator_to_array($listeners));

            $this->listenerProvider->remove(IntersectionParameterTypeDeclarationListener::class);

            $listeners = $this->listenerProvider->getListenersForEvent($event);
            self::assertCount(0, iterator_to_array($listeners));
        }
    }

    public function testProviderDetectsUnionTypes(): void
    {
        $this->listenerProvider->listen(UnionParameterTypeDeclarationListener::class);

        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $listeners = $this->listenerProvider->getListenersForEvent($event);
            self::assertCount(1, iterator_to_array($listeners));

            $this->listenerProvider->remove(UnionParameterTypeDeclarationListener::class);

            $listeners = $this->listenerProvider->getListenersForEvent($event);
            self::assertCount(0, iterator_to_array($listeners));
        }
    }

    public function testProviderImplementsProviderInterface(): void
    {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);
    }

    /**
     * @throws Throwable
     */
    public function testProviderListenToAllEvents(): void
    {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);

        $this->listenerProvider->listen(TestEventListener::class);

        self::assertCount(1, iterator_to_array($this->listenerProvider->getListenersForEvent(new TestEvent())));

        $this->listenerProvider->remove(TestEventListener::class);

        self::assertCount(0, iterator_to_array($this->listenerProvider->getListenersForEvent(new TestEvent())));
    }
}
