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
use function sprintf;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @template TEvent of object
     * @template TListener of class-string<(callable(TEvent):void)&object>
     *
     * @var array<class-string<TEvent>,array<class-string<(callable(TEvent):void)&TListener>,null>>
     */
    private array $listeners = [];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    /** @throws Throwable */
    public static function new(?ContainerInterface $container = null): self
    {
        $container ??= Container::getInstance();

        return $container->get(self::class);
    }

    /**
     * @template TEvent of object
     * @template TListener of class-string<(callable(TEvent):void)&object>
     *
     * @param TEvent $event
     *
     * @return Generator<TListener>
     */
    #[Override]
    public function getListenersForEvent(object $event): Generator
    {
        foreach ($this->listeners as $type => $listeners) {
            if (! $event instanceof $type && 'object' !== $type) {
                continue;
            }

            yield from array_keys($listeners);
        }
    }

    /**
     * @template TEvent of object
     * @template TListener of class-string<(callable(TEvent):void)&object>
     *
     * @param 'object'|class-string<TEvent>                   $event
     * @param class-string<(callable(TEvent):void)&TListener> $listener
     *
     * @throws ExceptionInterface
     */
    #[Override]
    public function listen(string $event, string $listener): void
    {
        $this->assertEvent($event);

        $this->assertListener($listener);

        if (array_key_exists($event, $this->listeners) && array_key_exists($listener, $this->listeners[$event])) {
            throw new ListenerAlreadyExistsException(sprintf(
                'Listener "%s" is already registered for event "%s".',
                $listener,
                $event,
            ));
        }

        $this->listeners[$event][$listener] = null;
    }

    /**
     * @template TEvent of object
     * @template TListener of class-string<(callable(TEvent):void)&object>
     *
     * @param TListener $listener
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
            throw new ListenerNotFoundException(sprintf('Listener "%s" not found.', $listener));
        }

        $this->container->unset($listener);
    }

    /**
     * @template TEvent of object
     *
     * @param 'object'|class-string<TEvent> $event
     *
     * @throws EventNotFoundException
     */
    private function assertEvent(string $event): void
    {
        match (true) {
            default => throw new EventNotFoundException(sprintf(
                'Event "%s" must be a class-string or "object".',
                $event,
            )),
            'object' === $event,
            class_exists($event),
            interface_exists($event),
            enum_exists($event)
            => null,
        };
    }

    /**
     * @template TEvent of object
     * @template TListener of class-string<(callable(TEvent):void)&object>
     *
     * @param TListener $listener
     *
     * @throws ListenerNotFoundException
     * @throws ListenerMissingInvokeMethodException
     */
    private function assertListener(string $listener): void
    {
        if (! class_exists($listener)) {
            throw new ListenerNotFoundException(sprintf(
                'Listener "%s" must be a class-string of an existing class.',
                $listener,
            ));
        }

        if (! method_exists($listener, '__invoke')) {
            throw new ListenerMissingInvokeMethodException(sprintf(
                'Listener "%s" must have an __invoke() method.',
                $listener,
            ));
        }
    }
}
