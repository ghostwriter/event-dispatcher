<?php

declare(strict_types=1);

namespace Tests\Unit\Container;

use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\EventDispatcher\Container\AbstractListenerProviderExtension;
use Ghostwriter\EventDispatcher\Event\ErrorOccurredEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\PHPUnitAssertions\Trait\AssertionsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(AbstractListenerProviderExtension::class)]
#[UsesClass(EventDispatcher::class)]
#[UsesClass(ErrorOccurredEvent::class)]
#[UsesClass(ListenerProvider::class)]
final class AbstractListenerProviderExtensionTest extends AbstractTestCase
{
    use AssertionsTrait;

    /** @throws Throwable */
    public function testImplementsGhostwriterContainerInterfaceServiceExtensionInterface(): void
    {
        self::assertClassImplementsInterface(AbstractListenerProviderExtension::class, ExtensionInterface::class);
    }
}
