<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Container\EventDispatcherProvider;
use Ghostwriter\EventDispatcher\Container\ListenerProviderFactory;
use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorOccurredEventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Fixture\Listener\MissingInvokeMethodListener;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[UsesClass(EventDispatcher::class)]
#[UsesClass(ListenerProviderFactory::class)]
#[UsesClass(ErrorOccurredEvent::class)]
#[UsesClass(EventDispatcherProvider::class)]
#[UsesClass(ListenerProvider::class)]
final class ListenerMissingInvokeMethodExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testListenThrowsListenerMissingInvokeMethodException(): void
    {
        $this->expectException(ListenerMissingInvokeMethodException::class);

        $this->listenerProvider->listen(ErrorOccurredEventInterface::class, MissingInvokeMethodListener::class);
    }
}
