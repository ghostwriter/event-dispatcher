<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Container;

use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ProviderInterface;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;
use Throwable;

/**
 * @see EventDispatcherTest
 */
final readonly class EventDispatcherProvider implements ProviderInterface
{
    /** @throws Throwable */
    #[Override]
    public function boot(ContainerInterface $container): void {}

    /** @throws Throwable */
    #[Override]
    public function register(BuilderInterface $builder): void
    {
        $builder->alias(EventDispatcherInterface::class, EventDispatcher::class);
        $builder->alias(ListenerProviderInterface::class, ListenerProvider::class);
        $builder->alias(PsrEventDispatcherInterface::class, EventDispatcherInterface::class);
        $builder->alias(PsrListenerProviderInterface::class, ListenerProviderInterface::class);
        $builder->factory(ListenerProvider::class, ListenerProviderFactory::class);
    }
}
