<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Contract;

use Generator;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;

/**
 * Maps registered Listeners.
 */
interface ListenerProviderInterface
{
    /**
     * @param callable(EventInterface<bool>):void            $listener
     * @param null|class-string<EventInterface<bool>>|string $event
     */
    public function addListener(
        callable $listener,
        int $priority = 0,
        ?string $event = null,
        ?string $id = null
    ): string;

    /**
     * @param class-string<EventInterface<bool>> $event
     * @param callable-string                    $listener
     *
     * @throws ContainerExceptionInterface
     */
    public function bindListener(string $event, string $listener, int $priority = 0, ?string $id = null): string;

    /**
     * @return Generator<ListenerInterface> an iterable of callables type-compatible with $event
     */
    public function getListenersForEvent(EventInterface $event): Generator;

    public function removeListener(string $listenerId): void;
}
