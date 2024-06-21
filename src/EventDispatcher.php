<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Override;
use Throwable;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ContainerInterface $container,
        #[Inject(ListenerProvider::class)]
        private ListenerProviderInterface $listenerProvider,
    ) {
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
        $isErrorEvent = $event instanceof ErrorEventInterface;

        foreach ($this->listenerProvider->listeners($event) as $listener) {
            try {
                $this->container->invoke($listener, [$event]);
            } catch (Throwable $throwable) {
                if ($isErrorEvent) {
                    /**
                     * If an error is raised while processing an ErrorEvent,
                     * re-throw the original throwable to prevent recursion.
                     *
                     * @var ErrorEventInterface $event
                     */
                    throw $event->throwable();
                }

                /** @var ErrorEventInterface&Event $errorEvent */
                $errorEvent = new ErrorEvent($event, $listener, $throwable);

                $this->dispatch($errorEvent);

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

        return match (true) {
            $listenerProvider instanceof ListenerProviderInterface => new self($container, $listenerProvider),
            default => $container->get(self::class),
        };
    }
}
