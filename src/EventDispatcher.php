<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Override;
use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ContainerInterface $container,
        private ListenerProviderInterface $listenerProvider,
    ) {}

    /** @throws Throwable */
    public static function new(
        ?ListenerProviderInterface $listenerProvider = null,
        ?ContainerInterface $container = null,
    ): self {
        $container ??= Container::getInstance();

        if ($listenerProvider instanceof ListenerProviderInterface) {
            $container->set($listenerProvider::class, $listenerProvider);
        }

        return $container->get(self::class);
    }

    /**
     * @template Event of object
     *
     * @param Event $event
     *
     * @throws Throwable
     *
     * @return Event
     */
    #[Override]
    public function dispatch(object $event): object
    {
        $isStoppable = $event instanceof StoppableEventInterface;
        if ($isStoppable && $event->isPropagationStopped()) {
            return $event;
        }

        $isErrorEvent = $event instanceof ErrorEventInterface;
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            try {
                $this->container->call($listener, [$event]);
            } catch (Throwable $throwable) {
                if ($isErrorEvent) {
                    /**
                     * If an error is raised while processing an ErrorEvent,
                     * re-throw the original throwable to prevent recursion.
                     *
                     * @var ErrorEventInterface
                     */
                    throw $event->throwable();
                }

                /** @var ErrorEventInterface&Event $errorEvent */
                $errorEvent = new ErrorEvent($event, $listener, $throwable);

                $this->dispatch($errorEvent);

                throw $throwable;
            }

            if (! $isStoppable) {
                continue;
            }

            if ($event->isPropagationStopped()) {
                break;
            }
        }

        return $event;
    }
}
