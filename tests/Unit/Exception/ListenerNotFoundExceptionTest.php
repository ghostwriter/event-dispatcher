<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListenerNotFoundException::class)]
#[CoversClass(ListenerProvider::class)]
final class ListenerNotFoundExceptionTest extends TestCase
{
    public function testRemoveThrowsListenerNotFoundException(): void
    {

        $provider = new ListenerProvider();

        $this->expectException(ListenerNotFoundException::class);

        $provider->remove(TestEventListener::class);
    }

    public function testBindThrowsListenerNotFoundException(): void
    {

        $provider = new ListenerProvider();

        $this->expectException(ListenerNotFoundException::class);

        $provider->bind(TestEvent::class, 'NonExistTestEventListener');
    }
}

