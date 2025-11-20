<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Container\Service\Definition\EventDispatcherDefinition;
use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorOccurredEventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\Listener\MissingInvokeMethodListener;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorOccurredEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(EventDispatcherDefinition::class)]
final class ListenerMissingInvokeMethodExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testListenThrowsListenerMissingInvokeMethodException(): void
    {
        $this->expectException(ListenerMissingInvokeMethodException::class);

        $this->listenerProvider->listen(ErrorOccurredEventInterface::class, MissingInvokeMethodListener::class);
    }
}
