<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\Container\Container;

final readonly class Listener implements ListenerInterface
{
    /**
     * @param \Closure(EventInterface<bool>):void $listener
     */
    public function __construct(
        private readonly \Closure $listener
    ) {
    }

    /**
     * @param EventInterface<bool> $event
     */
    public function __invoke(EventInterface $event): void
    {
        ($this->listener)($event);
    }

    /**
     * @param class-string|callable-string $invokable
     */
    public static function fromInvokableClass(string $invokable): self
    {
        /**
         * @var \Closure(EventInterface<bool>):void $listener
         */
        $listener = static fn (EventInterface $event): mixed => Container::getInstance()->call($invokable, [$event]);

        return new self($listener);
    }
}
