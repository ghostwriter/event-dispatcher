<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\ServiceProvider;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Contract\ServiceProviderInterface;
use Ghostwriter\EventDispatcher\Contract\DispatcherInterface;
use Ghostwriter\EventDispatcher\Contract\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;

final class EventDispatcherServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): void
    {
        $container->bind(ListenerProvider::class);
        $container->alias(PsrListenerProviderInterface::class, ListenerProvider::class);
        $container->alias(ListenerProviderInterface::class, ListenerProvider::class);

        $container->bind(Dispatcher::class);
        $container->alias(PsrEventDispatcherInterface::class, Dispatcher::class);
        $container->alias(DispatcherInterface::class, Dispatcher::class);
    }
}
