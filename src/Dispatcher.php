<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Contract\DispatcherInterface;
use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEventInterface;
use Throwable;

final class Dispatcher implements DispatcherInterface
{
    private PsrListenerProviderInterface $provider;

    public function __construct(?PsrListenerProviderInterface $psrListenerProvider = null)
    {
        $this->provider = $psrListenerProvider ?? new ListenerProvider();
    }

    /**
     * @psalm-suppress MixedMethodCall
     *
     * @throws Throwable
     */
    public function dispatch(object $event): object
    {
        $stoppable = $event instanceof PsrStoppableEventInterface;

        // If event propagation has stopped, return the event object passed.
        if ($stoppable && $event->isPropagationStopped()) {
            return $event;
        }

        /** @var callable(object):void $listener */
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
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

            if (! $stoppable) {
                continue;
            }

            if (! $event->isPropagationStopped()) {
                continue;
            }

            break;
        }

        return $event;
    }
}
