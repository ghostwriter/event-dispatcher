<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventListener;
use Tests\Unit\AbstractTestCase;
use NonExistentTestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(ListenerNotFoundException::class)]
final class ListenerNotFoundExceptionTest extends AbstractTestCase
{
    /**
     * @psalm-suppress UndefinedClass
     *
     * @throws Throwable
     */
    public function testListen(): void
    {
        $this->expectException(ListenerNotFoundException::class);

        $this->listenerProvider->listen(TestEvent::class, NonExistentTestEventListener::class);
    }

    public function testRemove(): void
    {
        $this->expectException(ListenerNotFoundException::class);

        $this->listenerProvider->forget(TestEventListener::class);
    }
}
