<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\DispatcherInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\Event\ErrorInterface;
use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\Provider;
use Ghostwriter\EventDispatcher\ProviderInterface;
use Throwable;

final readonly class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly ProviderInterface $listenerProvider = new Provider()
    ) {
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @return EventInterface<bool>
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        // If event propagation has stopped, return the event.
        if ($event->isStopped()) {
            return $event;
        }

        $isErrorEvent = $event instanceof ErrorInterface;

        foreach ($this->listenerProvider->listeners($event) as $listener) {
            try {
                $listener($event);

                if (! $event->isStopped()) {
                    continue;
                }

                return $event;
            } catch (Throwable $throwable) {
                if ($isErrorEvent) {
                    /**
                     * If an error is raised while processing an ErrorEvent,
                     * re-throw the original throwable to prevent recursion.
                     *
                     * @var ErrorEvent<bool> $event
                     */
                    throw $event->getThrowable();
                }

                $this->dispatch(new ErrorEvent($event, $listener, $throwable));

                throw $throwable;
            }
        }

        return $event;
    }

    public function listenerProvider(): ProviderInterface
    {
        return $this->listenerProvider;
    }
}
