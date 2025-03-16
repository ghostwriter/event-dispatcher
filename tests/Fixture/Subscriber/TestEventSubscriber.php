<?php

declare(strict_types=1);

namespace Tests\Fixture\Subscriber;

use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Interface\SubscriberInterface;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventListener;
use Throwable;
use function Tests\Fixture\listenerFunction;
use Tests\Fixture\Listener\LogTestEventExceptionMessageListener;
use Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface;

final readonly class TestEventSubscriber implements SubscriberInterface
{
    /**
     * @throws Throwable
     */
    #[\Override]
    public function __invoke(ListenerProviderInterface $listenerProvider): void
    {
        // Invokable class '::__invoke'
        $listenerProvider->bind(TestEvent::class, TestEventListener::class);
        $listenerProvider->bind(ErrorEventInterface::class, LogTestEventExceptionMessageListener::class);
    }
}
