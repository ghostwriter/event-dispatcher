<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\Listener\MissingInvokeMethodListener;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
final class ListenerMissingInvokeMethodExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testListenThrowsListenerMissingInvokeMethodException(): void
    {
        $this->expectException(ListenerMissingInvokeMethodException::class);

        $this->listenerProvider->bind(ErrorEventInterface::class, MissingInvokeMethodListener::class);
    }
}
