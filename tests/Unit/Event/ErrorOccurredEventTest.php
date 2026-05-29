<?php

declare(strict_types=1);

namespace Tests\Unit\Event;

use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorOccurredEventInterface;
use Ghostwriter\EventDispatcher\Interface\Event\StoppableEventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\PHPUnitAssertions\Trait\AssertionsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEventInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ErrorOccurredEvent::class)]
#[UsesClass(EventDispatcher::class)]
#[UsesClass(ListenerProvider::class)]
final class ErrorOccurredEventTest extends AbstractTestCase
{
    use AssertionsTrait;

    /** @throws Throwable */
    public function testImplementsGhostwriterEventDispatcherInterfaceEventErrorOccurredEventInterface(): void
    {
        self::assertClassImplementsInterface(ErrorOccurredEvent::class, ErrorOccurredEventInterface::class);
    }

    /** @throws Throwable */
    public function testImplementsGhostwriterEventDispatcherInterfaceEventStoppableEventInterface(): void
    {
        self::assertClassImplementsInterface(ErrorOccurredEvent::class, StoppableEventInterface::class);
    }

    /** @throws Throwable */
    public function testImplementsPsrEventDispatcherStoppableEventInterface(): void
    {
        self::assertClassImplementsInterface(ErrorOccurredEvent::class, PsrStoppableEventInterface::class);
    }
}
