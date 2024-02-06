<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface as ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface as ContainerExceptionInterface;
use Ghostwriter\Container\Reflector;
use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Interface\SubscriberInterface;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Throwable;

use const SORT_NUMERIC;

use function array_key_exists;
use function array_key_first;
use function array_reduce;
use function class_exists;
use function interface_exists;
use function is_a;
use function krsort;
use function method_exists;

/**
 * Maps registered Listeners, Providers and Subscribers.
 */
final class ListenerProvider implements ListenerProviderInterface
{
    public const LISTENERS = 0x0;

    public const SUBSCRIBERS = 0x1;

    /**
     * Map of registered Listeners and Subscribers.
     *
     * @var array{
     *    0: array<class-string<EventInterface<bool>>,array<int,array<class-string<object&callable(EventInterface<bool>):void>,bool>>>,
     *    1: array<class-string<SubscriberInterface>,bool>
     * }
     */
    private array $map = [
        self::LISTENERS => [],
        self::SUBSCRIBERS => [],
    ];

    public function __construct(
        private readonly Reflector $reflector = new Reflector(),
    ) {
    }

    /**
     * @param class-string<EventInterface<bool>>                       $event
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     *
     * @throws ExceptionInterface
     */
    public function bind(string $event, string $listener, int $priority = 0): void
    {
        self::assertEvent($event);

        self::assertListenerExists($event, $listener, $priority, $this->map[self::LISTENERS]);

        $this->map[self::LISTENERS][$event][$priority][$listener] = true;

        krsort($this->map[self::LISTENERS][$event], SORT_NUMERIC);
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @return Generator<class-string<(callable(EventInterface<bool>):void)&object>>
     */
    public function getListenersForEvent(EventInterface $event): Generator
    {
        if ($event->isPropagationStopped()) {
            return;
        }

        foreach ($this->map[self::LISTENERS] as $type => $priorities) {
            if (! $event instanceof $type) {
                continue;
            }

            foreach ($priorities as $priority) {
                foreach ($priority as $listener => $_) {
                    yield $listener;
                }
            }
        }
    }

    /**
     * @param class-string<EventInterface<bool>>                       $event
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     */
    public function hasListener(string $event, string $listener, int $priority): bool
    {
        return array_key_exists($event, $this->map[self::LISTENERS])
            && array_key_exists($priority, $this->map[self::LISTENERS][$event])
            && array_key_exists($listener, $this->map[self::LISTENERS][$event][$priority]);
    }

    /**
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     */
    public function listen(string $listener, int $priority = 0): void
    {
        foreach ($this->resolveEvents($listener) as $event) {
            $this->bind($event, $listener, $priority);
        }
    }

    /**
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     *
     * @throws ListenerNotFoundException
     */
    public function remove(string $listener): void
    {
        foreach ($this->map[self::LISTENERS] as $event => $listeners) {
            foreach ($listeners as $priority => $listenerId) {
                if (! array_key_exists($listener, $listenerId)) {
                    continue;
                }

                unset($this->map[self::LISTENERS][$event][$priority][$listener]);

                return;
            }
        }

        throw new ListenerNotFoundException($listener);
    }

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws SubscriberMustImplementSubscriberInterfaceException
     * @throws ContainerNotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function subscribe(string $subscriber): void
    {
        if (! is_a($subscriber, SubscriberInterface::class, true)) {
            throw new SubscriberMustImplementSubscriberInterfaceException($subscriber);
        }

        if (array_key_exists($subscriber, $this->map[self::SUBSCRIBERS])) {
            throw new SubscriberAlreadyRegisteredException($subscriber);
        }

        $this->map[self::SUBSCRIBERS][$subscriber] = true;

        Container::getInstance()->invoke($subscriber);
    }

    /**
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     *
     * @throws MissingEventParameterException
     * @throws MissingParameterTypeDeclarationException
     * @throws FailedToDetermineEventTypeException
     *
     * @return Generator<class-string<EventInterface<bool>>>
     */
    private function resolveEvents(string $listener): Generator
    {
        self::assertListener($listener);

        /** @var array<ReflectionParameter> $parameters */
        $parameters = $this->reflector
            ->reflectClass($listener)
            ->getMethod('__invoke')
            ->getParameters();

        if ($parameters === []) {
            throw new MissingEventParameterException('$event');
        }

        $parameter = $parameters[array_key_first($parameters)];

        $reflectionType = $parameter->getType();

        if ($reflectionType === null) {
            throw new MissingParameterTypeDeclarationException($parameter->getName());
        }

        /** @var Generator<class-string<EventInterface<bool>>> $events */
        $events = match (true) {
            $reflectionType instanceof ReflectionNamedType => [
                /** @var class-string<EventInterface<bool>> */
                $reflectionType->getName(),
            ],
            $reflectionType instanceof ReflectionIntersectionType,
            $reflectionType instanceof ReflectionUnionType => array_reduce(
                $reflectionType->getTypes(),
                /**
                 * @param array<class-string<EventInterface<bool>>> $events
                 *
                 * @return array<class-string<EventInterface<bool>>>
                 */
                static function (array $events, ReflectionType $reflectionType): array {
                    if ($reflectionType instanceof ReflectionNamedType) {
                        /** @var class-string<EventInterface<bool>> $name */
                        $name = $reflectionType->getName();

                        $events[] = $name;
                    }

                    return $events;
                },
                []
            ),
            default => throw new FailedToDetermineEventTypeException((string) $reflectionType),
        };

        yield from $events;
    }

    /**
     * @param class-string<EventInterface<bool>> $event
     *
     * @psalm-assert-if-true class-string<EventInterface<bool>> $event
     *
     * @throws EventMustImplementEventInterfaceException
     */
    private static function assertEvent(string $event): void
    {
        if (! class_exists($event) && ! interface_exists($event)) {
            throw new EventNotFoundException($event);
        }

        if (! is_a($event, EventInterface::class, true)) {
            throw new EventMustImplementEventInterfaceException($event);
        }
    }

    /**
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     *
     * @throws ListenerNotFoundException
     * @throws ListenerMissingInvokeMethodException
     */
    private static function assertListener(string $listener): void
    {
        if (! class_exists($listener)) {
            throw new ListenerNotFoundException($listener);
        }

        if (! method_exists($listener, '__invoke')) {
            throw new ListenerMissingInvokeMethodException($listener);
        }
    }

    /**
     * @param class-string<EventInterface<bool>>                       $event
     * @param class-string<(callable(EventInterface<bool>):void)&object> $listener
     *
     * @throws ListenerAlreadyExistsException
     */
    private static function assertListenerExists(
        string $event,
        string $listener,
        int $priority = 0,
        array $listeners = []
    ): void {
        self::assertListener($listener);

        if (
            array_key_exists($event, $listeners)
            && array_key_exists($priority, $listeners[$event])
            && array_key_exists($listener, $listeners[$event][$priority])
        ) {
            throw new ListenerAlreadyExistsException($listener);
        }
    }
}
