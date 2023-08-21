<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\Exception;

final class ListenerAlreadyExistsException extends \InvalidArgumentException implements Exception
{
}
