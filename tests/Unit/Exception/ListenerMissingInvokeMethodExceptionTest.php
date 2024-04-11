<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\MissingInvokeMethodListener;
use Ghostwriter\EventDispatcherTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
final class ListenerMissingInvokeMethodExceptionTest extends AbstractTestCase
{
    public function testListenThrowsListenerMissingInvokeMethodException(): void
    {
        $this->expectException(ListenerMissingInvokeMethodException::class);

        $this->listenerProvider->listen(EventInterface::class, MissingInvokeMethodListener::class);
    }
}
