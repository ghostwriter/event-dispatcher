<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use InvalidArgumentException;

final class ListenerNotFoundException extends InvalidArgumentException implements ExceptionInterface {}
