<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Container\ServiceProvider;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventListener;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(ListenerAlreadyExistsException::class)]
#[CoversClass(ServiceProvider::class)]
final class ListenerAlreadyExistsExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testListen(): void
    {
        $this->expectException(ListenerAlreadyExistsException::class);

        $this->listenerProvider->bind(TestEvent::class, TestEventListener::class);
        $this->listenerProvider->bind(TestEvent::class, TestEventListener::class);
    }
}
