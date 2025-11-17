<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\EventDispatcher\Container\Service\Definition\EventDispatcherDefinition;
use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

use function array_map;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(EventNotFoundException::class)]
#[CoversClass(ListenerAlreadyExistsException::class)]
#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[CoversClass(ListenerNotFoundException::class)]
#[CoversClass(SubscriberAlreadyRegisteredException::class)]
#[CoversClass(SubscriberMustImplementSubscriberInterfaceException::class)]
#[CoversClass(EventDispatcherDefinition::class)]
final class ExceptionsTest extends AbstractTestCase
{
    /** @var list<class-string<Throwable>> */
    public const array EXCEPTIONS = [
        EventNotFoundException::class,
        ListenerAlreadyExistsException::class,
        ListenerMissingInvokeMethodException::class,
        ListenerNotFoundException::class,
        SubscriberAlreadyRegisteredException::class,
        SubscriberMustImplementSubscriberInterfaceException::class,
    ];

    /** @throws Throwable */
    public function testExceptionsImplementExceptionInterface(): void
    {
        self::assertContainsOnlyInstancesOf(
            ExceptionInterface::class,
            array_map(
                /**
                 * @param class-string<ExceptionInterface> $exception
                 */
                static fn (string $exception): ExceptionInterface => new $exception(),
                self::EXCEPTIONS,
            ),
        );
    }
}
