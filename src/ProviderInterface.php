<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher;

use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;

/**
 * Maps registered Listeners.
 */
interface ProviderInterface
{
    /**
     * @param class-string<EventInterface<bool>> $event
     * @param class-string|callable-string       $listener
     *
     * @throws ExceptionInterface
     */
    public function bind(string $event, string $listener, int $priority = 0, string $id = null): string;

    /**
     * @param callable(EventInterface<bool>):void     $listener
     * @param class-string<EventInterface<bool>>|null $event
     *
     * @throws EventNotFoundException
     * @throws FailedToDetermineEventTypeException
     * @throws ListenerAlreadyExistsException
     * @throws MissingEventParameterException
     * @throws MissingParameterTypeDeclarationException
     * @throws ExceptionInterface
     */
    public function listen(callable $listener, int $priority = 0, string $event = null, string $id = null): string;

    /**
     * @param EventInterface<bool> $event
     *
     * @return \Generator<ListenerInterface>
     */
    public function listeners(EventInterface $event): \Generator;

    public function remove(string $listenerId): void;

    /**
     * @param class-string<SubscriberInterface> $subscriber
     *
     * @throws SubscriberMustImplementSubscriberInterfaceException
     */
    public function subscribe(string $subscriber): void;
}
