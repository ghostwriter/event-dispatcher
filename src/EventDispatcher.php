<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Throwable;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ContainerInterface $container,
        private ListenerProviderInterface $listenerProvider,
    ) {}

    /**
     * @template TEvent of object
     *
     * @param TEvent $event
     *
     * @throws Throwable
     *
     * @return TEvent
     */
    public function dispatch(object $event): object
    {
        $isEvent = $event instanceof ErrorEventInterface;

        if ($isEvent && $event->isPropagationStopped()) {
            return $event;
        }

        $isErrorEvent = $event instanceof ErrorEventInterface;

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            try {
                $this->container->invoke($listener, [$event]);

                if ($isEvent && ! $event->isPropagationStopped()) {
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

    /**
     * @throws Throwable
     */
    public static function new(?ListenerProviderInterface $listenerProvider = null): self
    {
        $container = Container::getInstance();

        if (! $container->has(EventServiceProvider::class)) {
            $container->provide(EventServiceProvider::class);
        }

        if ($listenerProvider !== null) {
            return new self($container, $listenerProvider);
        }

        return $container->get(self::class);
    }
}
