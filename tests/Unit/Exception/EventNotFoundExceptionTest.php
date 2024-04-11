<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use Tests\Fixture\TestEventListener;
use Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(EventNotFoundException::class)]
final class EventNotFoundExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testThrowsEventNotFoundException(): void
    {
        $this->expectException(EventNotFoundException::class);

        $this->listenerProvider->listen('does-not-exist', TestEventListener::class);
    }
}
