<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Fiber;
use Generator;
use Ghostwriter\EventDispatcher\Contract\DispatcherInterface;
use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Throwable;

final class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly ListenerProviderInterface $listenerProvider = new ListenerProvider()
    ) {
    }

    public function dispatch(EventInterface $event): EventInterface
    {
        // If event propagation has stopped, return the event.
        if ($event->isPropagationStopped()) {
            return $event;
        }

        $fiber = new Fiber(function (EventInterface $event, Generator $listeners): EventInterface {
            $isErrorEventInterface = $event instanceof ErrorEventInterface;
            /** @var ListenerInterface $listener */
            foreach ($listeners as $listener) {
                try {
                    $listener($event);

                    // If event propagation has stopped, return the event.
                    if ($event->isPropagationStopped()) {
                        return $event;
                    }

                    Fiber::suspend();
                } catch (Throwable $throwable) {
                    // If an error is raised while processing an ErrorEvent,
                    // re-throw the original throwable to prevent recursion.
                    if ($isErrorEventInterface) {
                        throw $event->getThrowable();
                    }

                    // Dispatch a new ErrorEvent with the unhandled error raised.
                    $this->dispatch(new ErrorEvent($event, $listener, $throwable));

                    // Rethrow the original throwable; per PSR-14 specification.
                    throw $throwable;
                }
            }

            return $event;
        });

        $fiber->start($event, $this->listenerProvider->getListenersForEvent($event));

        while ($fiber->isSuspended()) {
            $fiber->resume();
        }

        return $fiber->getReturn();
    }

    public function getListenerProvider(): ListenerProviderInterface
    {
        return $this->listenerProvider;
    }
}
