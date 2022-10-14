<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\Contract\EventDispatcherExceptionInterface;
use RuntimeException;

final class FailedToDetermineTypeDeclarationsException extends RuntimeException implements EventDispatcherExceptionInterface
{
    public static function invalidTypeDeclarations(string $name, string $type): self
    {
        return new self(sprintf('Invalid type declarations for "$%s" parameter; %s given.', $name, $type));
    }

    public static function missingFirstParameter(): self
    {
        return new self('Missing first parameter, "$event".');
    }

    public static function missingTypeDeclarations(string $name): self
    {
        return new self(sprintf('Missing type declarations for "$%s" parameter.', $name));
    }
}
