# Event Dispatcher

[![Compliance](https://github.com/ghostwriter/event-dispatcher/actions/workflows/compliance.yml/badge.svg)](https://github.com/ghostwriter/event-dispatcher/actions/workflows/compliance.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/event-dispatcher?color=8892bf)](https://www.php.net/supported-versions)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/event-dispatcher/coverage.svg)](https://shepherd.dev/github/ghostwriter/event-dispatcher)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/event-dispatcher)](https://packagist.org/packages/ghostwriter/event-dispatcher)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/event-dispatcher?color=blue)](https://packagist.org/packages/ghostwriter/event-dispatcher)

Provides an Event Dispatcher implementation for PHP inspired by [PSR-14](https://www.php-fig.org/psr/psr-14/) specification.

## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/event-dispatcher
```

## Usage

Registering and dispatching an Event Listener.

```php
use Ghostwriter\EventDispatcher\AbstractEvent;
use Ghostwriter\EventDispatcher\Dispatcher;
use Ghostwriter\EventDispatcher\ListenerProvider;

class ExampleEvent extends AbstractEvent
{
}

$listener = function (ExampleEvent $event) : void {
    // do something
};

$listenerProvider = new ListenerProvider();
$listenerProvider->addListener($listener)

$dispatcher = new Dispatcher($listenerProvider);
$dispatcher->dispatch(new SomeEvent());
```

### Event Subscriber

Registering an Event Subscriber.

```php
use Ghostwriter\EventDispatcher\Contract\SubscriberInterface;

class Subscriber implements SubscriberInterface{
    /**
     * @throws Throwable
     */
    public function __invoke(ListenerProviderInterface $listenerProvider): void
    {
        $priority = 0;
        $listenerProvider->addListenerService(
            TestEvent::class,
            TestEventListener::class,
            $priority,
            'InvokableListener'
        );

        $listenerProvider->addListener(
            [new TestEventListener, 'onTest'],
            $priority,
            TestEvent::class,
            'CallableArrayInstanceListener'
        );

        $listenerProvider->addListener(
            static function (TestEventInterface $testEvent): void {
                $testEvent->write(__METHOD__);
            },
            $priority,
            TestEvent::class,
            'AnonymousFunctionListener'
        );

        $listenerProvider->addListener(
            'Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction',
            $priority,
            TestEvent::class,
            'FunctionListener'
        );

        $listenerProvider->addListener(
            TestEventListener::class.'::onStatic',
            $priority,
            TestEvent::class,
            'StaticMethodListener'
        );

        $listenerProvider->addListener(
            [TestEventListener::class, 'onStaticCallableArray'],
            $priority,
            TestEvent::class,
            'CallableArrayStaticMethodListener'
        );
    }
}

$listenerProvider = new ListenerProvider();

$subscriber = new Subscriber();

$listenerProvider->addSubscriber($subscriber);

$dispatcher = new Dispatcher($listenerProvider);

$dispatcher->dispatch(new TestEvent());
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` instead of using the issue tracker.

## Thank you

Thank you for freely sharing your knowledge and free time with me in [Laminas Chat](https://laminas.dev/chat).

- [Matthew Weier O'Phinney](https://github.com/weierophinney)

## Credits

- [Nathanael Esayeas](https://github.com/ghostwriter)
- [All Contributors](https://github.com/ghostwriter/event-dispatcher/contributors)

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.
