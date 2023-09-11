<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\ExceptionInterface;
use InvalidArgumentException;

final class FailedToDetermineEventTypeException extends InvalidArgumentException implements ExceptionInterface
{
}
