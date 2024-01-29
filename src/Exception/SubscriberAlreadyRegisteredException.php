<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\Interface\EventDispatcherExceptionInterface;
use InvalidArgumentException;

final class SubscriberAlreadyRegisteredException extends InvalidArgumentException implements EventDispatcherExceptionInterface
{
}
