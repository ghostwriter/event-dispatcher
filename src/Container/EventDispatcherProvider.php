<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Container;

use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Service\Provider\AbstractProvider;
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
final class EventDispatcherProvider extends AbstractProvider
{
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
