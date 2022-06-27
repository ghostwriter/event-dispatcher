<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\Contract\EventDispatcherExceptionInterface;
use RuntimeException;

final class ShouldNotHappenException extends RuntimeException implements EventDispatcherExceptionInterface
{
}
