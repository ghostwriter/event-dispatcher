<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit;

use Closure;
use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException;
use Ghostwriter\EventDispatcher\Interface\EventInterface;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\IntersectionParameterTypeDeclarationListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\MissingParameterTypeDeclarationListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\Listener\UnionParameterTypeDeclarationListener;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEvent2;
use Ghostwriter\EventDispatcher\Tests\Fixture\TestEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreMethodForCodeCoverage;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(ListenerProvider::class)]
#[IgnoreMethodForCodeCoverage(ListenerProvider::class, 'createReflectionFunction')]
#[Small]
final class ListenerProviderTest extends TestCase
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
     * @return Generator<string,list{0:callable|callable-string|Closure,1?:0,2?:string}>
     */
    public static function supportedListenersDataProvider(): iterable
    {
        yield from [
        //            'AnonymousFunctionListenerMissingClosureParamType' => [
        //                static fn (EventInterface $event) => \assert(TestEvent::class === $event::class),
        //                self::PRIORITY,
        //                TestEvent::class,
        //            ],
        //            'AnonymousFunctionListener' => [
        //                static function (TestEvent $testEvent): void {
        //                    $testEvent->write($testEvent::class);
        //                },
        //            ],
            'FunctionListener' => ['Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction'],
            'StaticMethodListener' => [TestEventListener::class . '::onStatic'],
        //            'CallableArrayStaticMethodListener' => [[TestEventListener::class, 'onStaticCallableArray']],
        //            'CallableArrayInstanceListener' => [[new TestEventListener(), 'onTest']],
        //            'InvokableListener' => [new TestEventListener()],
            'InvokableClass' => [TestEventListener::class],
        ];
    }

    /**
     * @throws Throwable
     */
    public function testListenRaisesExceptionIfUnableToDetermineEventType(): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->expectException(MissingParameterTypeDeclarationException::class);
        $this->expectExceptionMessage('event');

        $this->provider->listen(MissingParameterTypeDeclarationListener::class);
    }

    public function testProviderBind(): void
    {
        $testEvent = new TestEvent();

        self::assertSame('', $testEvent->read());

        self::assertInstanceOf(ListenerProviderInterface::class, $this->provider);


        $this->provider->bind(TestEvent::class, TestEventListener::class);

        $listeners = $this->provider->getListenersForEvent($testEvent);

        foreach ($listeners as $listener) {
            Container::getInstance()->call($listener, [$testEvent]);
        }

        self::assertSame(TestEventListener::class . '::__invoke', $testEvent->read());

        $this->provider->remove(TestEventListener::class);

        self::assertCount(0, iterator_to_array($this->provider->getListenersForEvent($testEvent)));
    }

    /**
     * @param class-string<EventInterface>|null $event
     */
    #[DataProvider('supportedListenersDataProvider')]
    public function testProviderDetectsEventType(
        string $listener,
        int $priority = 0,
    ): void {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->provider);

        $listeners = iterator_to_array($this->provider->getListenersForEvent(new TestEvent()));

        self::assertCount(0, $listeners);

        $this->provider->listen($listener, $priority);


        $listeners = iterator_to_array($this->provider->getListenersForEvent(new TestEvent()));

        self::assertCount(1, $listeners);

        $this->provider->remove($listener);


        $listeners = iterator_to_array($this->provider->getListenersForEvent(new TestEvent()));
        self::assertCount(0, $listeners);
    }


    /**
     * @throws Throwable
     */
    public function testProviderListenToAllEvents(): void
    {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->provider);

        $this->provider->listen(TestEventListener::class);

        self::assertCount(1, iterator_to_array($this->provider->getListenersForEvent(new TestEvent())));

        $this->provider->remove(TestEventListener::class);

        self::assertCount(0, iterator_to_array($this->provider->getListenersForEvent(new TestEvent())));
    }

    public function testProviderDetectsIntersectionTypes(): void
    {
        $this->provider->listen(IntersectionParameterTypeDeclarationListener::class);

        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $listeners = $this->provider->getListenersForEvent($event);
            self::assertCount(1, iterator_to_array($listeners));

            $this->provider->remove(IntersectionParameterTypeDeclarationListener::class);

            $listeners = $this->provider->getListenersForEvent($event);
            self::assertCount(0, iterator_to_array($listeners));
        }
    }

    public function testProviderDetectsUnionTypes(): void
    {
        $this->provider->listen(UnionParameterTypeDeclarationListener::class);

        foreach ([new TestEvent(), new TestEvent2()] as $event) {
            $listeners = $this->provider->getListenersForEvent($event);
            self::assertCount(1, iterator_to_array($listeners));

            $this->provider->remove(UnionParameterTypeDeclarationListener::class);

            $listeners = $this->provider->getListenersForEvent($event);
            self::assertCount(0, iterator_to_array($listeners));
        }
    }

    public function testProviderImplementsProviderInterface(): void
    {
        self::assertInstanceOf(ListenerProviderInterface::class, $this->provider);
    }
}
