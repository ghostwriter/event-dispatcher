<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventNotFoundException::class)]
#[CoversClass(ListenerProvider::class)]
final class EventNotFoundExceptionTest extends TestCase
{
    public function testThrowsEventNotFoundException(): void
    {
        $provider = new ListenerProvider();

        $this->expectException(EventNotFoundException::class);

        $provider->bind('does-not-exist', TestEventListener::class);
    }
}
