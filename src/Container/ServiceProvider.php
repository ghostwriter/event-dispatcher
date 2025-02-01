<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Container;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Override;
use Throwable;

final readonly class ServiceProvider implements ServiceProviderInterface
{
    public const array ALIASES = [
        EventDispatcher::class => EventDispatcherInterface::class,
        ListenerProvider::class => ListenerProviderInterface::class,
    ];

    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        foreach (self::ALIASES as $service => $alias) {
            $container->alias($service, $alias);
        }
    }
}
