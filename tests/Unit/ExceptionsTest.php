<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\ListenerMissingInvokeMethodException;
use Ghostwriter\EventDispatcher\Exception\ListenerNotFoundException;
use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\Exception\SubscriberAlreadyRegisteredException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(EventMustImplementEventInterfaceException::class)]
#[CoversClass(EventNotFoundException::class)]
#[CoversClass(FailedToDetermineEventTypeException::class)]
#[CoversClass(ListenerAlreadyExistsException::class)]
#[CoversClass(ListenerMissingInvokeMethodException::class)]
#[CoversClass(ListenerNotFoundException::class)]
#[CoversClass(MissingEventParameterException::class)]
#[CoversClass(MissingParameterTypeDeclarationException::class)]
#[CoversClass(SubscriberAlreadyRegisteredException::class)]
#[CoversClass(SubscriberMustImplementSubscriberInterfaceException::class)]
final class ExceptionsTest extends TestCase
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
        MissingEventParameterException::class,
        MissingParameterTypeDeclarationException::class,
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
