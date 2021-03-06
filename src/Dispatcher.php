<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\DispatcherInterface;
use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Throwable;

final class Dispatcher implements DispatcherInterface
{
    private ListenerProviderInterface $listenerProvider;

    public function __construct(?ListenerProviderInterface $listenerProvider = null)
    {
        $this->listenerProvider = $listenerProvider ?? new ListenerProvider();
    }

    /**
     * @throws Throwable
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        // If event propagation has stopped, return the event object passed.
        if ($event->isPropagationStopped()) {
            return $event;
        }

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            try {
                $listener($event);
            } catch (Throwable $throwable) {
                // If an error is raised while processing an ErrorEvent,
                // re-throw the original throwable to prevent recursion.
                if ($event instanceof ErrorEventInterface) {
                    throw $event->getThrowable();
                }

                // Dispatch a new ErrorEvent with the unhandled error raised.
                $this->dispatch(new ErrorEvent($event, $listener, $throwable));

                // Rethrow the original throwable; per PSR-14 specification.
                throw $throwable;
            }

            if ($event->isPropagationStopped()) {
                break;
            }
        }

        return $event;
    }
}
