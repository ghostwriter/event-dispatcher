<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\MissingEventParameterListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\MissingParameterTypeDeclarationListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MissingEventParameterException::class)]
#[CoversClass(ListenerProvider::class)]
final class MissingEventParameterExceptionTest extends TestCase
{
    public function testThrowsMissingParameterTypeDeclarationException(): void
    {
        $provider = new ListenerProvider();

        $this->expectException(MissingEventParameterException::class);

        $provider->listen(MissingEventParameterListener::class);
    }
}

