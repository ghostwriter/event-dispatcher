<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\ExceptionInterface;
use RuntimeException;

final class ListenerMissingInvokeMethodException extends RuntimeException implements ExceptionInterface
{
}
