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

final class EventDispatcherServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): void
    {
        $container->bind(ListenerProvider::class);
        $container->alias(ListenerProviderInterface::class, ListenerProvider::class);

        $container->set(Dispatcher::class, static fn (ContainerInterface $container): object => $container->build(
            Dispatcher::class,
            [
                'listenerProvider'=> $container->get(ListenerProviderInterface::class),
            ]
        ));
        $container->alias(DispatcherInterface::class, Dispatcher::class);
    }
}
