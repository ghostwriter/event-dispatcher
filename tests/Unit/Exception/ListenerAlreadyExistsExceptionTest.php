<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListenerProvider::class)]
#[CoversClass(ListenerAlreadyExistsException::class)]
final class ListenerAlreadyExistsExceptionTest extends TestCase
{
    public function testListenThrowsListenerAlreadyExistsException(): void {

        $provider = new ListenerProvider();

        $this->expectException(ListenerAlreadyExistsException::class);

        $provider->listen(TestEventListener::class);
        $provider->listen(TestEventListener::class);
    }

    public function testBindThrowsListenerAlreadyExistsException(): void {

        $provider = new ListenerProvider();

        $this->expectException(ListenerAlreadyExistsException::class);

        $provider->bind(TestEvent::class,TestEventListener::class);
        $provider->bind(TestEvent::class, TestEventListener::class);
    }
}

