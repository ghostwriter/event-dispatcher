<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Tests\Fixture\Listener\MissingInvokeMethodListener;
use Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
final class ListenerMissingInvokeMethodExceptionTest extends AbstractTestCase
{
    public function testListenThrowsListenerMissingInvokeMethodException(): void
    {
        $this->expectException(ListenerMissingInvokeMethodException::class);

        $this->listenerProvider->listen(EventInterface::class, MissingInvokeMethodListener::class);
    }
}
