<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Container;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Override;
use Throwable;

use function assert;

/**
 * @see AbstractListenerProviderExtensionTest
 *
 * @implements ExtensionInterface<ListenerProvider>
 */
abstract readonly class AbstractListenerProviderExtension implements ExtensionInterface
{
    /** @var array<'object'|class-string,list<class-string>> */
    public const array LISTENERS = [
        'object' => [],
    ];

    /**
     * @param ListenerProviderInterface $service
     *
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container, object $service): void
    {
        assert($service instanceof ListenerProviderInterface);

        foreach (static::LISTENERS as $event => $listeners) {
            foreach ($listeners as $listener) {
                $service->listen($event, $listener);
            }
        }
    }
}
