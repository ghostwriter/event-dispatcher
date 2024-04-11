<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\EventDispatcher\Event\ErrorEvent;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\EventServiceProvider;
use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Trait\EventTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

use function is_a;
use function sprintf;

#[CoversClass(EventDispatcher::class)]
#[CoversClass(ErrorEvent::class)]
#[CoversClass(EventServiceProvider::class)]
#[CoversClass(EventTrait::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(EventMustImplementEventInterfaceException::class)]
#[CoversClass(EventNotFoundException::class)]
#[CoversClass(FailedToDetermineEventTypeException::class)]
#[CoversClass(ListenerAlreadyExistsException::class)]
#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[CoversClass(ListenerNotFoundException::class)]
#[CoversClass(SubscriberAlreadyRegisteredException::class)]
#[CoversClass(SubscriberMustImplementSubscriberInterfaceException::class)]
final class ExceptionsTest extends AbstractTestCase
{
    /**
     * @var array<class-string<Throwable>>
     */
    public const EXCEPTIONS = [
        EventMustImplementEventInterfaceException::class,
        EventNotFoundException::class,
        FailedToDetermineEventTypeException::class,
        ListenerAlreadyExistsException::class,
        ListenerMissingInvokeMethodException::class,
        ListenerNotFoundException::class,
        SubscriberAlreadyRegisteredException::class,
        SubscriberMustImplementSubscriberInterfaceException::class,
    ];

    public function testExceptionsImplementExceptionInterface(): void
    {
        foreach (self::EXCEPTIONS as $exception) {
            self::assertTrue(
                is_a($exception, ExceptionInterface::class, true),
                sprintf('Exception "%s" does not implement "%s"', $exception, ExceptionInterface::class)
            );
        }
    }
}
