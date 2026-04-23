<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Container;

use Ghostwriter\Container\Service\Provider\AbstractProvider;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;

/**
 * @see EventDispatcherTest
 */
final class EventDispatcherProvider extends AbstractProvider
{
    /**
     * alias => service.
     *
     * @var array<class-string,class-string>
     */
    public const array ALIAS = [
        EventDispatcherInterface::class => EventDispatcher::class,
        ListenerProviderInterface::class => ListenerProvider::class,
        PsrEventDispatcherInterface::class => EventDispatcherInterface::class,
        PsrListenerProviderInterface::class => ListenerProviderInterface::class,
    ];
}
