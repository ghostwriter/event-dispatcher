<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\MissingParameterTypeDeclarationListener;
use Ghostwriter\EventDispatcherTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(MissingParameterTypeDeclarationException::class)]
final class MissingParameterTypeDeclarationExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testListen(): void
    {
        $this->expectException(MissingParameterTypeDeclarationException::class);
        $this->expectExceptionMessage('event');

        $this->listenerProvider->listen(MissingParameterTypeDeclarationListener::class);
    }
}
