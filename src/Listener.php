<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Closure;
use Ghostwriter\Container\Container;
use Throwable;

final readonly class Listener implements ListenerInterface
{
    /**
     * @param Closure(EventInterface<bool>):void $listener
     */
    public function __construct(
        private readonly Closure $listener
    ) {
    }

    /**
     * @param class-string|callable-string $invokable
     */
    public static function fromInvokableClass(string $invokable): self
    {
        /**
         * @var Closure(EventInterface<bool>):void $listener
         * @throws Throwable
         */
        $listener = static function (EventInterface $event) use ($invokable): void {
            Container::getInstance()->call($invokable, [$event]);
        };

        return new self($listener);
    }

    /**
     * @param EventInterface<bool> $event
     */
    public function __invoke(EventInterface $event): void
    {
        ($this->listener)($event);
    }
}
