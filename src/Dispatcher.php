<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Fiber;
use Ghostwriter\EventDispatcher\DispatcherInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\Event\ErrorInterface;
use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\ListenerProviderInterface;
use Throwable;

final readonly class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly ListenerProviderInterface $listenerProvider = new ListenerProvider()
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
        if ($event->isPropagationStopped()) {
            return $event;
        }

        $fiber = new Fiber(
            /**
             * @param EventInterface<bool> $event
             *
             * @return EventInterface<bool>
             */
            function (EventInterface $event): EventInterface {
                $isErrorEvent = $event instanceof ErrorInterface;

                foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
                    try {
                        $listener($event);

                        // If event propagation has stopped, break to return the event.
                        if ($event->isPropagationStopped()) {
                            /**
                             * @var EventInterface<true> $event
                             */
                            return $event;
                        }
                        Fiber::suspend();
                    } catch (Throwable $throwable) {
                        // If an error is raised while processing an ErrorEvent,
                        // re-throw the original throwable to prevent recursion.
                        if ($isErrorEvent) {
                            /**
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

                /**
                 * @var EventInterface<false> $event
                 */
                return $event;
            }
        );

        $fiber->start($event);

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
