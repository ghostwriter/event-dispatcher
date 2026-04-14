<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Container;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Override;
use Throwable;

/**
 * @see ListenerProviderFactoryTest
 *
 * @implements FactoryInterface<ListenerProvider>
 */
final readonly class ListenerProviderFactory implements FactoryInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): ListenerProvider
    {
        return new ListenerProvider($container);
    }
}
