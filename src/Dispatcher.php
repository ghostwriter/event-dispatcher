<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\Event\ErrorEventInterface;
use Throwable;

final readonly class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private ProviderInterface $provider = new Provider()
    ) {
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @return EventInterface<bool>
     *
     * @throws \Throwable
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        // If event propagation has stopped, return the event.
        if ($event->isStopped()) {
            return $event;
        }

        $isErrorEvent = $event instanceof ErrorEventInterface;

        foreach ($this->provider->listeners($event) as $listener) {
            try {
                $listener($event);

                if (!$event->isStopped()) {
                    continue;
                }

                return $event;
            } catch (\Throwable $throwable) {
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
}
