<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\Container;
use Ghostwriter\EventDispatcher\Interface\DispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Throwable;

final readonly class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private ListenerProviderInterface $provider = new ListenerProvider()
    ) {
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @return EventInterface<bool>
     *
     * @throws Throwable
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        if ($event->isPropagationStopped()) {
            return $event;
        }

        $isErrorEvent = $event instanceof ErrorEventInterface;

        /**
         * @var callable(EventInterface<bool>):void $listener
         */
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            try {
                Container::getInstance()->call($listener, [$event]);

                if (!$event->isPropagationStopped()) {
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
}
