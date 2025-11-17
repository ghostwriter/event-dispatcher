<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Container\Service\Definition;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Override;

final readonly class EventDispatcherDefinition implements DefinitionInterface
{
    public const array ALIASES = [
        EventDispatcherInterface::class => EventDispatcher::class,
        ListenerProviderInterface::class => ListenerProvider::class,
    ];

    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        foreach (self::ALIASES as $alias => $service) {
            $container->alias($service, $alias);
        }
    }
}
