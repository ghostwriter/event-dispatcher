<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
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
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
final class ListenerProviderTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testProviderBind(): void
    {
        $testEvent = new TestEvent();

        self::assertSame('', $testEvent->read());

        self::assertInstanceOf(ListenerProviderInterface::class, $this->listenerProvider);

        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, $testEvent);

        $container = Container::getInstance();

        $listeners = $this->listenerProvider->getListenersForEvent($testEvent);
        foreach ($listeners as $listener) {
            $container->invoke($listener, [$testEvent]);
        }

        self::assertSame(TestEventListener::class . '::__invoke', $testEvent->read());

        $this->listenerProvider->forget(TestEventListener::class);

        $this->assertListenersCount(0, $testEvent);
    }

    /**
     * @throws Throwable
     */
    public function testProviderDetectsEventType(): void
    {
        $testEvent = new TestEvent();

        $this->assertListenersCount(0, $testEvent);

        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, $testEvent);

        $this->listenerProvider->forget(TestEventListener::class);

        $this->assertListenersCount(0, $testEvent);
    }

    public function testProviderDetectsIntersectionTypes(): void
    {
        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $this->assertListenersCount(0, $event);

            $this->listenerProvider->listen(TestEvent::class, IntersectionParameterTypeDeclarationListener::class);
            $this->listenerProvider->listen(TestEvent2::class, IntersectionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(1, $event);

            $this->listenerProvider->forget(IntersectionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(0, $event);
        }
    }

    public function testProviderDetectsUnionTypes(): void
    {
        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $this->assertListenersCount(0, $event);

            $this->listenerProvider->listen(TestEvent::class, UnionParameterTypeDeclarationListener::class);
            $this->listenerProvider->listen(TestEvent2::class, UnionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(1, $event);

            $this->listenerProvider->forget(UnionParameterTypeDeclarationListener::class);

            $this->assertListenersCount(0, $event);
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

        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);

        $this->assertListenersCount(1, new TestEvent());

        $this->listenerProvider->forget(TestEventListener::class);

        $this->assertListenersCount(0, new TestEvent());
    }
}
