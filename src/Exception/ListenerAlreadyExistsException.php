<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\ExceptionInterface;

final class ListenerAlreadyExistsException extends \InvalidArgumentException implements ExceptionInterface
{
}
