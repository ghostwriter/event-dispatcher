<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Container\ListenerProviderFactory;
use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Fixture\TestEventListener;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(EventNotFoundException::class)]
#[UsesClass(ErrorOccurredEvent::class)]
#[UsesClass(EventDispatcher::class)]
#[UsesClass(ListenerProvider::class)]
#[UsesClass(ListenerProviderFactory::class)]
final class EventNotFoundExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testThrowsEventNotFoundException(): void
    {
        $this->expectException(EventNotFoundException::class);

        /** @psalm-suppress ArgumentTypeCoercion */
        $this->listenerProvider->listen('does-not-exist', TestEventListener::class);
    }
}
