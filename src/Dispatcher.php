<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Generator;
use Ghostwriter\EventDispatcher\Contract\DispatcherInterface;
use Ghostwriter\EventDispatcher\Contract\ErrorEventInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEventInterface;
use Throwable;

final class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private ?PsrListenerProviderInterface $listenerProvider = null
    ) {
        $this->listenerProvider ??= new ListenerProvider();
    }

    /**
     * Provide all relevant listeners an event to process.
     *
     * @template TEvent of object
     *
     * @param TEvent $event the object to process
     *
     * @throws Throwable
     *
     * @psalm-suppress MixedMethodCall
     *
     * @return TEvent the Event that was passed, now modified by listeners
     */
    public function dispatch(object $event): object
    {
        $stoppable = $event instanceof PsrStoppableEventInterface;
        // If event propagation has stopped, return the event object passed.
        if ($stoppable && $event->isPropagationStopped()) {
            return $event;
        }

        /** @var Generator<callable(TEvent):void> $listeners */
        $listeners = $this->getListenerProvider()
            ->getListenersForEvent($event);
        foreach ($listeners as $listener) {
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

            if ($event->isPropagationStopped()) {
                // Tell the $listeners \Generator to stop yielding Listeners for $event.
                $listeners->send($stoppable);
            }
        }

        return $event;
    }
}
