<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Closure;
use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ReflectorException;
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
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Throwable;
use function array_key_exists;
use const SORT_NUMERIC;

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
     *    0: array<class-string<EventInterface<bool>>,array<int,array<string,callable(EventInterface<bool>):void>>>,
     *    1: array<class-string<SubscriberInterface>,bool>
     * }
     */
    private array $map = [
        self::LISTENERS => [],
        self::SUBSCRIBERS => []
    ];

    /**
     * @param class-string<EventInterface<bool>> $event
     * @param class-string|callable-string $listener
     *
     * @throws ExceptionInterface
     * @throws EventMustImplementEventInterfaceException
     * @throws EventNotFoundException
     * @throws FailedToDetermineEventTypeException
     * @throws ListenerAlreadyExistsException
     * @throws ListenerMissingInvokeMethodException
     * @throws ListenerNotFoundException
     * @throws MissingEventParameterException
     * @throws MissingParameterTypeDeclarationException
     * @throws Throwable
     */
    public function bind(
        string $event,
        string $listener,
        int    $priority = 0,
    ): void {
        if (!class_exists($event) && !interface_exists($event)) {
            throw new EventNotFoundException($event);
        }

        if (!is_a($event, EventInterface::class, true)) {
            throw new EventMustImplementEventInterfaceException();
        }

        if (!class_exists($listener)) {
            throw new ListenerNotFoundException($listener);
        }

        if (!method_exists($listener, '__invoke')) {
            throw new ListenerMissingInvokeMethodException($listener);
        }

        /**
         * @psalm-suppress UnsupportedPropertyReferenceUsage
         */
        $map = &$this->map;
        if (array_key_exists($event, $map[self::LISTENERS])
            && array_key_exists($priority, $map[self::LISTENERS][$event])
            && array_key_exists($listener, $map[self::LISTENERS][$event][$priority])
        ) {
            throw new ListenerAlreadyExistsException($listener);
        }

        /** @var callable(EventInterface<bool>):void $callable */
        $callable = Container::getInstance()->get($listener);

        $map[self::LISTENERS][$event][$priority][$listener] = $callable;

        krsort($map[self::LISTENERS][$event], SORT_NUMERIC);
    }

    /**
     * @template TListenId of string
     *
     * class-string param can only target to named or callable objects in docblock, what about function/callables?
     *
     * @param class-string|callable-string $listener
     *
     * @throws ExceptionInterface
     * @throws EventNotFoundException
     * @throws FailedToDetermineEventTypeException
     * @throws ListenerAlreadyExistsException
     * @throws MissingEventParameterException
     * @throws MissingParameterTypeDeclarationException
     * @throws Throwable
     */
    public function listen(
        string $listener,
        int    $priority = 0,
    ): void {
        if (class_exists($listener) && !method_exists($listener, '__invoke')) {
            throw new ListenerMissingInvokeMethodException($listener);
        }

        /**
         * @psalm-suppress UnsupportedPropertyReferenceUsage
         */
        $map = &$this->map;

        /**
         * @var callable(EventInterface<bool>):void $callable
         */
        $callable = is_callable($listener) ? $listener : Container::getInstance()->get($listener);

        foreach ($this->resolveEvents($callable(...)) as $event) {
            if (array_key_exists($event, $map[self::LISTENERS])
                && array_key_exists($priority, $map[self::LISTENERS][$event])
                && array_key_exists($listener, $map[self::LISTENERS][$event][$priority])
            ) {
                throw new ListenerAlreadyExistsException($listener);
            }

            $this->map[self::LISTENERS][$event][$priority][$listener] = $callable;

            krsort($this->map[self::LISTENERS][$event], SORT_NUMERIC);
        }
    }

    public function __construct(
        private readonly Reflector $reflector = new Reflector(),
    ){
    }

    /**
     * @param Closure(EventInterface<bool>):void $closure
     *
     * @return Generator<class-string<EventInterface<bool>>>
     *
     * @throws MissingEventParameterException
     * @throws MissingParameterTypeDeclarationException
     * @throws FailedToDetermineEventTypeException
     * @throws ReflectorException
     */
    private function resolveEvents(Closure $closure): Generator
    {
        $reflectionFunction = $this->reflector->reflectFunction($closure);

        $parameters = $reflectionFunction->getParameters();
        if ([] === $parameters) {
            throw new MissingEventParameterException('$event');
        }

        foreach ($parameters as $parameter) {
            $reflectionType = $parameter->getType();

            if ($reflectionType === null) {
                throw new MissingParameterTypeDeclarationException($parameter->getName());
            }

            if ($reflectionType instanceof ReflectionNamedType)
            {
                /** @var class-string<EventInterface<bool>> $name */
                $name = $reflectionType->getName();

                yield $name;

                break;
            }

            /**
             * @var Generator<class-string<EventInterface<bool>>> $events
             */
            $events = match (true) {
                $reflectionType instanceof ReflectionIntersectionType,
                    $reflectionType instanceof ReflectionUnionType => $this->eventFromReflectionIntersectionOrUnionType($reflectionType),
                default => throw new FailedToDetermineEventTypeException((string)$reflectionType),
            };

            yield from $events;

            break;
        }
    }

    /**
     * @return array<class-string<EventInterface<bool>>>
     */
    private function eventFromReflectionIntersectionOrUnionType(
        ReflectionIntersectionType|ReflectionUnionType $reflectionIntersectionOrUnionType
    ): array {

        return array_reduce(
            $reflectionIntersectionOrUnionType->getTypes(),
            /**
             * @param array<class-string<EventInterface<bool>>> $events
             * @return array<class-string<EventInterface<bool>>>
             */
            static function (array $events, ReflectionType $reflectionType): array {
                if ($reflectionType instanceof ReflectionNamedType) {
                    /** @var class-string<EventInterface<bool>> $name */
                    $name = $reflectionType->getName();

                    $events[] = $name;
                }

                /**
                 * @var array<class-string<EventInterface<bool>>> $events
                 */
                return $events;
            },
            []
        );
    }

    /**
     * @param EventInterface<bool> $event
     *
     * @return Generator<callable(EventInterface<bool>):void>
     */
    public function getListenersForEvent(EventInterface $event): Generator
    {
        foreach ($this->map[self::LISTENERS] as $type => $priorities) {
            if (!$event instanceof $type) {
                continue;
            }

            foreach ($priorities as $priority) {
                foreach ($priority as $listener) {
                    yield $listener;
                }
            }
        }
    }

    /**
     * @param class-string|callable-string $listenerId
     *
     * @throws ListenerNotFoundException
     */
    public function remove(string $listenerId): void
    {
        foreach ($this->map[self::LISTENERS] as $event => $listeners) {
            foreach ($listeners as $priority => $listener) {
                if (array_key_exists($listenerId, $listener)) {
                    unset($this->map[self::LISTENERS][$event][$priority][$listenerId]);

                    return;
                }
            }
        }

        throw new ListenerNotFoundException($listenerId);
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
        if (!is_a($subscriber, SubscriberInterface::class, true)) {
            throw new SubscriberMustImplementSubscriberInterfaceException($subscriber);
        }

        /**
         * @psalm-suppress UnsupportedPropertyReferenceUsage
         */
        $map = &$this->map;
        if (array_key_exists($subscriber, $map[self::SUBSCRIBERS])) {
            throw new SubscriberAlreadyRegisteredException($subscriber);
        }

        $map[self::SUBSCRIBERS][$subscriber] = true;

        Container::getInstance()->invoke($subscriber, [$this]);
    }
}
