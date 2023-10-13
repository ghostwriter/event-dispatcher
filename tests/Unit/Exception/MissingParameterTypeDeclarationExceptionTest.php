<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\MissingParameterTypeDeclarationListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MissingParameterTypeDeclarationException::class)]
#[CoversClass(ListenerProvider::class)]
final class MissingParameterTypeDeclarationExceptionTest extends TestCase
{
    public function testThrowsMissingParameterTypeDeclarationException(): void
    {
        $provider = new ListenerProvider();

        $this->expectException(MissingParameterTypeDeclarationException::class);

        $provider->listen(MissingParameterTypeDeclarationListener::class);
    }
}

