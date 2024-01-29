<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcherTests\Unit\Exception;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcherTests\Fixture\Listener\MissingEventParameterListener;
use Ghostwriter\EventDispatcherTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(MissingEventParameterException::class)]
final class MissingEventParameterExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testThrowsMissingParameterTypeDeclarationException(): void
    {
        $this->expectException(MissingEventParameterException::class);

        $this->listen(MissingEventParameterListener::class);
    }
}
