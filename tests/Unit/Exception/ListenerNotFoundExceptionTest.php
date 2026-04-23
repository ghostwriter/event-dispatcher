<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Container\EventDispatcherProvider;
use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use NonExistentTestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventListener;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ListenerNotFoundException::class)]
#[UsesClass(ErrorOccurredEvent::class)]
#[UsesClass(EventDispatcher::class)]
#[UsesClass(EventDispatcherProvider::class)]
#[UsesClass(ListenerProvider::class)]
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

    /** @throws Throwable */
    public function testRemove(): void
    {
        $this->expectException(ListenerNotFoundException::class);

        $this->listenerProvider->remove(TestEventListener::class);
    }
}
