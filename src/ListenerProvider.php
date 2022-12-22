<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Closure;
use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\EventDispatcher\Contract\EventInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Contract\SubscriberInterface;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineTypeDeclarationsException;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use const SORT_NUMERIC;
use function array_key_exists;
use function class_exists;
use function interface_exists;
use function is_object;
use function is_string;
use function is_subclass_of;
use function krsort;
use function spl_object_hash;
use function sprintf;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * Map of registered Listeners, Providers and Subscribers.
     *
     * @var array<class-string<EventInterface<bool>>,array<int,array<string,Listener>>>
     */
    private array $listeners = [];

    public function __construct(
        private ?ContainerInterface $container = null
    ) {
    }

    public function addListener(
        callable $listener,
        int $priority = 0,
        ?string $event = null,
        ?string $id = null
    ): string {
        $id ??= $this->getListenerId($listener);
        $events = $this->getEventType($listener, $event);
        foreach ($events as $event) {
            if (! class_exists($event) && ! interface_exists($event)) {
                throw new InvalidArgumentException(sprintf('Event "%s" cannot be found.', $event));
            }

            if (
                array_key_exists($event, $this->listeners) &&
                array_key_exists($priority, $this->listeners[$event]) &&
                array_key_exists($id, $this->listeners[$event][$priority])
            ) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate Listener "%s" detected for "%s" Event.',
                    $id,
                    $event
                ));
            }

            /** @var class-string<EventInterface<bool>> $event */
            $this->listeners[$event][$priority][$id] = new Listener($listener);

            krsort($this->listeners[$event], SORT_NUMERIC);
        }

        return $id;
    }

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addSubscriber(string $subscriber): void
    {
        if (! is_subclass_of($subscriber, SubscriberInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Subscriber with ID "%s" must implement %s.',
                $subscriber,
                SubscriberInterface::class
            ));
        }

        ($this->getContainer()->get($subscriber))($this);
    }

    public function bindListener(string $event, string $listener, int $priority = 0, ?string $id = null): string
    {
        if (! is_subclass_of($event, EventInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Event "%s" must implement %s.',
                $event,
                EventInterface::class
            ));
        }

        return $this->addListener(
            fn (EventInterface $event): mixed => $this->getContainer()
                ->get($listener)($event),
            $priority,
            $event,
            $id
        );
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container ??= Container::getInstance();
    }

    /**
     * @return Generator<Listener>
     */
    public function getListenersForEvent(EventInterface $event): Generator
    {
        foreach ($this->listeners as $type => $priorities) {
            if (! $event instanceof $type) {
                continue;
            }

            /** @var array<int, int> $priorities */
            foreach ($priorities as $priority) {
                /** @var array<int, Listener> $priority */
                foreach ($priority as $listener) {
                    /** @var null|string $stop */
                    $stop = yield $listener;
                    if (PHP_EOL === $stop) {
                        // event propagation has stopped
                        return;
                    }
                }
            }
        }
    }

    public function removeListener(string $listenerId): void
    {
        foreach ($this->listeners as $event => $listeners) {
            foreach ($listeners as $priority => $listener) {
                if (array_key_exists($listenerId, $listener)) {
                    unset($this->listeners[$event][$priority][$listenerId]);
                    return;
                }
            }
        }

        throw new InvalidArgumentException(sprintf('Listener "%s" cannot be found.', $listenerId));
    }

    /**
     * Resolves the class type of the first argument on a callable.
     *
     * @param callable(EventInterface<bool>):void $listener
     *
     * @throws FailedToDetermineTypeDeclarationsException if $listener is missing type-declarations
     * @throws FailedToDetermineTypeDeclarationsException if $listener class does not exist
     *
     * @return Generator<class-string<EventInterface<bool>>|string>
     */
    private function getEventType(callable $listener, ?string $event = null): Generator
    {
        if (null !== $event) {
            yield $event;
            return;
        }

        try {
            $parameters = (new ReflectionFunction(Closure::fromCallable($listener)))->getParameters();
        } catch (ReflectionException $reflectionException) {
            throw new FailedToDetermineTypeDeclarationsException(
                $reflectionException->getMessage(),
                $reflectionException->getCode(),
                $reflectionException
            );
        }

        if ([] === $parameters) {
            throw FailedToDetermineTypeDeclarationsException::missingFirstParameter();
        }

        yield from array_map(static function (ReflectionParameter $reflectionParameter): mixed {
            /** @var null|ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType $reflectionType */
            $reflectionType = $reflectionParameter->getType();

            if (! $reflectionType instanceof ReflectionType) {
                throw FailedToDetermineTypeDeclarationsException::missingTypeDeclarations(
                    $reflectionParameter->getName()
                );
            }

            if ($reflectionType instanceof ReflectionNamedType) {
                return $reflectionType->getName();
            }

            /** @var array<ReflectionNamedType> $reflectionTypeTypes */
            $reflectionTypeTypes = $reflectionType->getTypes();
            return array_map(
                static fn (
                    ReflectionNamedType $reflectionNamedType
                ): string => $reflectionNamedType->getName(),
                $reflectionTypeTypes
            );
        }, $parameters);
    }

    /**
     * Derives a unique ID from the listener.
     *
     * @param callable(EventInterface<bool>):void $listener
     */
    private function getListenerId(callable $listener): string
    {
        /** @var array{0:object|string,1:string}|object|string $listener */
        return match (true) {
            // Function callables are strings, so use that directly.
            is_string($listener) => $listener,
            /** @var object $listener */
            is_object($listener) => sprintf('listener.%s', spl_object_hash($listener)),
            // Object callable represents a method on an object.
            is_object($listener[0]) => sprintf('%s::%s', $listener[0]::class, $listener[1]),
            // Class callable represents a static class method.
            default => sprintf('%s::%s', $listener[0], $listener[1])
        };
    }
}
