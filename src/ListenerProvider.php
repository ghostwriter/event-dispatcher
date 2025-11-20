<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Override;
use Throwable;

use function array_key_exists;
use function array_keys;
use function class_exists;
use function enum_exists;
use function interface_exists;
use function method_exists;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @template Event of object
     * @template Listener of object
     *
     * @var array<class-string<Event>,array<class-string<(callable(Event):void)&Listener>,null>>
     */
    private array $listeners = [];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    /** @throws Throwable */
    public static function new(): self
    {
        return Container::getInstance()->get(self::class);
    }

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param Event $event
     *
     * @return Generator<class-string<(callable(Event):void)&Listener>>
     */
    #[Override]
    public function getListenersForEvent(object $event): Generator
    {
        foreach ($this->listeners as $type => $listeners) {
            if (! $event instanceof $type) {
                continue;
            }

            foreach (array_keys($listeners) as $listener) {
                yield $listener;
            }
        }
    }

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param 'object'|class-string<Event>                  $event
     * @param class-string<(callable(Event):void)&Listener> $listener
     *
     * @throws ExceptionInterface
     */
    #[Override]
    public function listen(string $event, string $listener): void
    {
        $this->assertEvent($event);

        $this->assertListener($listener);

        if (array_key_exists($event, $this->listeners) && array_key_exists($listener, $this->listeners[$event])) {
            throw new ListenerAlreadyExistsException($listener);
        }

        $this->listeners[$event][$listener] = null;
    }

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param class-string<(callable(Event):void)&Listener> $listener
     *
     * @throws ListenerNotFoundException
     */
    #[Override]
    public function remove(string $listener): void
    {
        $removed = false;

        foreach ($this->listeners as $event => $listeners) {
            if (! array_key_exists($listener, $listeners)) {
                continue;
            }

            unset($this->listeners[$event][$listener]);

            $removed = true;
        }

        if (! $removed) {
            throw new ListenerNotFoundException($listener);
        }

        $this->container->unset($listener);
    }

    /**
     * @template Event of object
     *
     * @param class-string<Event>|string $event
     *
     * @psalm-assert class-string<Event> $event
     *
     * @throws EventNotFoundException
     */
    private function assertEvent(string $event): void
    {
        match (true) {
            default => throw new EventNotFoundException($event),
            'object' === $event,
            class_exists($event),
            interface_exists($event),
            enum_exists($event)
            => null,
        };
    }

    /**
     * @template Event of object
     * @template Listener of object
     *
     * @param class-string<(callable(Event):void)&Listener> $listener
     *
     * @psalm-assert class-string<(callable(Event):void)&Listener> $listener
     *
     * @throws ListenerNotFoundException
     * @throws ListenerMissingInvokeMethodException
     */
    private function assertListener(string $listener): void
    {
        if (! class_exists($listener)) {
            throw new ListenerNotFoundException($listener);
        }

        if (! method_exists($listener, '__invoke')) {
            throw new ListenerMissingInvokeMethodException($listener);
        }
    }
}
