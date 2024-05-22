<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Tests\Fixture\Listener\IntersectionParameterTypeDeclarationListener;
use Tests\Fixture\Listener\UnionParameterTypeDeclarationListener;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEvent2;
use Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
final class ListenerProviderTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testProviderBind(): void
    {
        $testEvent = new TestEvent();

        self::assertEmpty($testEvent->read());

        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);

        $this->listenerProvider->bind(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, $testEvent);

        $container = Container::getInstance();

        $listeners = $this->listenerProvider->listeners($testEvent);
        foreach ($listeners as $listener) {
            $container->invoke($listener, [$testEvent]);
        }

        self::assertCount(1, $testEvent->read());

        $this->listenerProvider->unbind(TestEventListener::class);

        $this->assertListenersCount(0, $testEvent);
    }

    /**
     * @throws Throwable
     */
    public function testProviderDetectsEventType(): void
    {
        $testEvent = new TestEvent();

        $this->assertListenersCount(0, $testEvent);

        $this->listenerProvider->bind(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, $testEvent);

        $this->listenerProvider->unbind(TestEventListener::class);

        $this->assertListenersCount(0, $testEvent);
    }

    /**
     * @throws Throwable
     */
    public function testProviderDetectsIntersectionTypes(): void
    {
        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $this->assertListenersCount(0, $event);

            $this->listenerProvider->bind(TestEvent::class, IntersectionParameterTypeDeclarationListener::class);
            $this->listenerProvider->bind(TestEvent2::class, IntersectionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(1, $event);

            $this->listenerProvider->unbind(IntersectionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(0, $event);
        }
    }

    /**
     * @throws Throwable
     */
    public function testProviderDetectsUnionTypes(): void
    {
        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $this->assertListenersCount(0, $event);

            $this->listenerProvider->bind(TestEvent::class, UnionParameterTypeDeclarationListener::class);
            $this->listenerProvider->bind(TestEvent2::class, UnionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(1, $event);

            $this->listenerProvider->unbind(UnionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(0, $event);
        }
    }

    /**
     * @throws Throwable
     */
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

        $this->listenerProvider->bind(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, new TestEvent());

        $this->listenerProvider->unbind(TestEventListener::class);

        $this->assertListenersCount(0, new TestEvent());
    }
}
