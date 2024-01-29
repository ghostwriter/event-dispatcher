<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Throwable;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    private ContainerInterface $container;

    public function __construct(
        private ListenerProviderInterface $listenerProvider = new ListenerProvider()
    ) {
        $this->container = Container::getInstance();

        if ($this->container->has(EventServiceProvider::class)) {
            return;
        }

        $this->container->provide(EventServiceProvider::class);
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @throws Throwable
     *
     * @return EventInterface<bool>
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        if ($event->isPropagationStopped()) {
            return $event;
        }

        $isErrorEvent = $event instanceof ErrorEventInterface;

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            try {
                $this->container->invoke($listener, [$event]);

                if (! $event->isPropagationStopped()) {
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
