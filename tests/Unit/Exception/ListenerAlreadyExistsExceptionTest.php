<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Container\EventDispatcherProvider;
use Ghostwriter\EventDispatcher\Container\ListenerProviderFactory;
use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventListener;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ListenerAlreadyExistsException::class)]
#[UsesClass(EventDispatcher::class)]
#[UsesClass(ListenerProviderFactory::class)]
#[UsesClass(ErrorOccurredEvent::class)]
#[UsesClass(EventDispatcherProvider::class)]
#[UsesClass(ListenerProvider::class)]
final class ListenerAlreadyExistsExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testListen(): void
    {
        $this->expectException(ListenerAlreadyExistsException::class);

        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);
        $this->listenerProvider->listen(TestEvent::class, TestEventListener::class);
    }
}
