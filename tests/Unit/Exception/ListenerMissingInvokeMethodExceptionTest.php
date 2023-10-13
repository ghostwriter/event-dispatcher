<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\MissingInvokeMethodListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[CoversClass(ListenerProvider::class)]
final class ListenerMissingInvokeMethodExceptionTest extends TestCase
{
    public function testListenThrowsListenerMissingInvokeMethodException(): void
    {

        $provider = new ListenerProvider();

        $this->expectException(ListenerMissingInvokeMethodException::class);

        $provider->listen(MissingInvokeMethodListener::class);
    }
    public function testBindThrowsListenerMissingInvokeMethodException(): void
    {

        $provider = new ListenerProvider();

        $this->expectException(ListenerMissingInvokeMethodException::class);

        $provider->bind(EventInterface::class, MissingInvokeMethodListener::class);
    }
}

