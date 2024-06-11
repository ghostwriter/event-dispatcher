<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use RuntimeException;

final class SubscriberNotFoundException extends RuntimeException implements ExceptionInterface
{
}
