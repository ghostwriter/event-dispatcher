<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit\Exception;

use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Ghostwriter\EventDispatcherTests\Fixture\TestEvent;
use Ghostwriter\EventDispatcherTests\Fixture\TestEventListener;
use Ghostwriter\EventDispatcherTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(AbstractEvent::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(ListenerNotFoundException::class)]
final class ListenerNotFoundExceptionTest extends AbstractTestCase
{
    /**
     * @psalm-suppress UndefinedClass
     *
     * @throws Throwable
     */
    public function testBind(): void
    {
        $provider = new ListenerProvider();

        $this->expectException(ListenerNotFoundException::class);

        $provider->bind(TestEvent::class, 'NonExistTestEventListener');
    }

    public function testRemove(): void
    {
        $provider = new ListenerProvider();

        $this->expectException(ListenerNotFoundException::class);

        $provider->remove(TestEventListener::class);
    }
}
