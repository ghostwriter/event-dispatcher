<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Throwable;

final readonly class EventDispatcher implements Dispatcher
{
    public function __construct(
        private readonly ListenerProvider $listenerProvider = new EventListenerProvider()
    ) {
    }

    /**
     * @param Event<bool> $event
     *
     * @return Event<bool>
     */
    public function dispatch(Event $event): Event
    {
        // If event propagation has stopped, return the event.
        if ($event->isPropagationStopped()) {
            return $event;
        }

        $fiber = new \Fiber(
            /**
             * @param Event<bool> $event
             *
             * @return Event<bool>
             */
            function (Event $event): Event {
                $isErrorEventInterface = $event instanceof ErrorEvent;

                foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
                    try {
                        $listener($event);

                        // If event propagation has stopped, return the event.
                        if ($event->isPropagationStopped()) {
                            /*
                             * @var Event<bool> $event
                             */
                            return $event;
                        }

                        \Fiber::suspend();
                    } catch (\Throwable $throwable) {
                        // If an error is raised while processing an ErrorEvent,
                        // re-throw the original throwable to prevent recursion.
                        if ($isErrorEventInterface) {
                            /*
                             * @var ErrorEvent<bool> $event
                             */
                            throw $event->getThrowable();
                        }

                        // Dispatch a new ErrorEvent with the unhandled error raised.
                        $this->dispatch(new ErrorEvent($event, $listener, $throwable));

                        // Rethrow the original throwable; per PSR-14 specification.
                        throw $throwable;
                    }
                }

                return $event;
            }
        );

        $fiber->start($event);
        while ($fiber->isSuspended()) {
            $fiber->resume();
        }

        /*
         * @var Event<bool>
         */
        return $fiber->getReturn();
    }

    public function getListenerProvider(): ListenerProvider
    {
        return $this->listenerProvider;
    }
}
