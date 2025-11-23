<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Container;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;
use Throwable;

final readonly class EventDispatcherDefinition implements DefinitionInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $container->alias(EventDispatcher::class, EventDispatcherInterface::class);
        $container->alias(EventDispatcherInterface::class, PsrEventDispatcherInterface::class);
        $container->alias(ListenerProvider::class, ListenerProviderInterface::class);
        $container->alias(ListenerProviderInterface::class, PsrListenerProviderInterface::class);
    }
}
