<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Exception;

use Ghostwriter\EventDispatcher\Contract\EventDispatcherExceptionInterface;
use InvalidArgumentException;

final class ListenerNotFoundException extends InvalidArgumentException implements EventDispatcherExceptionInterface
{
    public function __construct(string $listenerId)
    {
        parent::__construct(sprintf('Listener "%s" cannot be found.', $listenerId));
    }
}
