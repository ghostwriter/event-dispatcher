<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Generator;
use Ghostwriter\EventDispatcher\EventInterface;
use Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException;
use Ghostwriter\EventDispatcher\Exception\EventNotFoundException;
use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use Ghostwriter\EventDispatcher\Exception\ListenerAlreadyExistsException;
use Ghostwriter\EventDispatcher\Exception\MissingEventParameterException;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\Exception\SubscriberMustImplementSubscriberInterfaceException;
use Ghostwriter\EventDispatcher\ExceptionInterface;
use Ghostwriter\EventDispatcher\Listener;
use Ghostwriter\EventDispatcher\ListenerInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use function is_a;
use function is_subclass_of;
use function iterator_to_array;

#[CoversClass(ListenerProvider::class)]
#[CoversClass(Listener::class)]
#[Small]
final class ListenerProviderTest extends PHPUnitTestCase
{
    /**
     * @var int
     */
    private const PRIORITY = 0;

    public ListenerProviderInterface $provider;

    protected function setUp(): void
    {
        $this->provider = new ListenerProvider();
    }

    /**
     * @return \Generator<class-string<\Throwable>, array{class-string<\Throwable>}>
     */
    public static function exceptionsDataProvider(): \Generator
    {
        $exceptionClasses = [
            EventMustImplementEventInterfaceException::class,
            EventNotFoundException::class,
            FailedToDetermineEventTypeException::class,
            ListenerAlreadyExistsException::class,
            MissingEventParameterException::class,
            MissingParameterTypeDeclarationException::class,
            SubscriberMustImplementSubscriberInterfaceException::class,
        ];

        foreach ($exceptionClasses as $exceptionClass) {
            yield $exceptionClass => [$exceptionClass];
        }
    }

    /**
     * @param class-string<\Throwable> $class
     */
    #[DataProvider('exceptionsDataProvider')]
    public function testExceptionsImplementExceptionInterface(string $class): void
    {
        self::assertTrue(is_a($class, ExceptionInterface::class, true));
        self::assertTrue(is_subclass_of($class, ExceptionInterface::class, true));
    }

    /**
     * @return Generator<string,array{Closure(Ghostwriter\EventDispatcher\EventInterface): mixed,int,class-string<Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent>}|array{Closure(Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent): void}|array{string}|array{<missing>}|array{array<int,mixed>}|array{Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener}>
     */
    public static function supportedListenersDataProvider(): iterable
    {
        yield 'AnonymousFunctionListenerMissingClosureParamType' => [
            static fn (EventInterface $event): mixed => self::assertSame(TestEvent::class, $event::class),
            self::PRIORITY,
            TestEvent::class,
        ];

        yield 'AnonymousFunctionListener' => [
            static function (TestEvent $testEvent): void {
                $testEvent->write($testEvent::class);
            },
        ];

        yield 'FunctionListener' => ['Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction'];

        yield 'StaticMethodListener' => [TestEventListener::class.'::onStatic'];

        yield 'CallableArrayStaticMethodListener' => [[TestEventListener::class, 'onStaticCallableArray']];

        yield 'CallableArrayInstanceListener' => [[new TestEventListener(), 'onTest']];

        yield 'InvokableListener' => [new TestEventListener()];
    }

    public function testListenRaisesExceptionIfUnableToDetermineEventType(): void
    {
        /** @psalm-suppress MissingClosureParamType */
        $listener = static function ($testEvent): void {
            if ($testEvent instanceof TestEvent) {
                $testEvent->write($testEvent::class);
            }
        };

        $this->expectException(ExceptionInterface::class);
        $this->expectException(MissingParameterTypeDeclarationException::class);
        $this->expectExceptionMessage('testEvent');
        $this->provider->addListener($listener);
    }

    /**
     * @param array{0:object|string,1:string}|callable $listener
     */
    #[DataProvider('supportedListenersDataProvider')]
    public function testProviderDetectsEventType(
        array|callable $listener,
        int $priority = 0,
        string $event = null
    ): void {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->provider);

        /** @var callable(object):void $listener */
        $listenerId = $this->provider->addListener($listener, $priority, $event);

        $listeners = $this->provider->getListenersForEvent(new TestEvent());

        self::assertInstanceOf(ListenerInterface::class, $listeners->current());

        self::assertSame($listener, $listeners->current()->getListener());

        $this->provider->removeListener($listenerId);

        self::assertCount(0, iterator_to_array($this->provider->getListenersForEvent(new TestEvent())));
    }
}
