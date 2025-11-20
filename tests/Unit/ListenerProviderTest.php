<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\EventDispatcher\Container\Service\Definition\EventDispatcherDefinition;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\Listener\IntersectionParameterTypeDeclarationListener;
use Tests\Fixture\Listener\UnionParameterTypeDeclarationListener;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEvent2;
use Tests\Fixture\TestEventListener;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(EventDispatcherDefinition::class)]
final class ListenerProviderTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testProviderBind(): void
    {
        self::assertEmpty($this->testEvent->read());

        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);

        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, $this->testEvent);

        $listeners = $this->listenerProvider->getListenersForEvent($this->testEvent);
        foreach ($listeners as $listener) {
            $this->container->call($listener, [$this->testEvent]);
        }

        self::assertCount(1, $this->testEvent->read());

        $this->listenerProvider->unbind(TestEventListener::class);

        $this->assertListenersCount(0, $this->testEvent);
    }

    /** @throws Throwable */
    public function testProviderDetectsEventType(): void
    {
        $this->assertListenersCount(0, $this->testEvent);

        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, $this->testEvent);

        $this->listenerProvider->unbind(TestEventListener::class);

        $this->assertListenersCount(0, $this->testEvent);
    }

    /** @throws Throwable */
    public function testProviderDetectsIntersectionTypes(): void
    {
        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $this->assertListenersCount(0, $event);

            $this->listenerProvider->listen(TestEvent::class, IntersectionParameterTypeDeclarationListener::class);
            $this->listenerProvider->listen(TestEvent2::class, IntersectionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(1, $event);

            $this->listenerProvider->unbind(IntersectionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(0, $event);
        }
    }

    /** @throws Throwable */
    public function testProviderDetectsUnionTypes(): void
    {
        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $this->assertListenersCount(0, $event);

            $this->listenerProvider->listen(TestEvent::class, UnionParameterTypeDeclarationListener::class);
            $this->listenerProvider->listen(TestEvent2::class, UnionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(1, $event);

            $this->listenerProvider->unbind(UnionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(0, $event);
        }
    }

    /** @throws Throwable */
    public function testProviderImplementsProviderInterface(): void
    {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);
    }

    /** @throws Throwable */
    public function testProviderListenToAllEvents(): void
    {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);

        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, new TestEvent());

        $this->listenerProvider->unbind(TestEventListener::class);

        $this->assertListenersCount(0, new TestEvent());
    }
}
