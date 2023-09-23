# Event Dispatcher

[![Compliance](https://github.com/ghostwriter/event-dispatcher/actions/workflows/compliance.yml/badge.svg)](https://github.com/ghostwriter/event-dispatcher/actions/workflows/compliance.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/event-dispatcher?color=8892bf)](https://www.php.net/supported-versions)
[![GitHub Sponsors](https://img.shields.io/github/sponsors/ghostwriter?label=Sponsor+@ghostwriter/event-dispatcher&logo=GitHub+Sponsors)](https://github.com/sponsors/ghostwriter)
[![Mutation Coverage](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fghostwriter%2Fevent-dispatcher%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/ghostwriter/event-dispatcher/main)
[![Code Coverage](https://codecov.io/gh/ghostwriter/event-dispatcher/branch/main/graph/badge.svg)](https://codecov.io/gh/ghostwriter/event-dispatcher)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/event-dispatcher/coverage.svg)](https://shepherd.dev/github/ghostwriter/event-dispatcher)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/event-dispatcher)](https://packagist.org/packages/ghostwriter/event-dispatcher)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/event-dispatcher?color=blue)](https://packagist.org/packages/ghostwriter/event-dispatcher)

Provides an Event Dispatcher implementation for PHP.

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
use Ghostwriter\EventDispatcher\Provider;

final class ExampleEvent extends AbstractEvent
{
}

$listener = function (ExampleEvent $event) : void {
    // do something
};

$provider = new Provider();
$provider->listen($listener)

$dispatcher = new Dispatcher($provider);
$dispatcher->dispatch(new ExampleEvent());
```

### Event Subscriber

Registering an Event Subscriber.

```php
use Ghostwriter\EventDispatcher\SubscriberInterface;

final class EventSubscriber implements SubscriberInterface {
    /**
     * @throws Throwable
     */
    public function __invoke(ProviderInterface $provider): void
    {
        $priority = 0;

        $provider->bind(
            TestEvent::class,
            TestEventListener::class,
            $priority,
            'InvokableListener'
        );

        $provider->listen(
            [new TestEventListener, 'onTest'],
            $priority,
            TestEvent::class,
            'CallableArrayInstanceListener'
        );

        $provider->listen(
            static function (TestEventInterface $testEvent): void {
                $testEvent->write(__METHOD__);
            },
            $priority,
            TestEvent::class,
            'AnonymousFunctionListener'
        );

        $provider->listen(
            'Ghostwriter\EventDispatcher\Tests\Fixture\listenerFunction',
            $priority,
            TestEvent::class,
            'FunctionListener'
        );

        $provider->listen(
            TestEventListener::class.'::onStatic',
            $priority,
            TestEvent::class,
            'StaticMethodListener'
        );

        $provider->listen(
            [TestEventListener::class, 'onStaticCallableArray'],
            $priority,
            TestEvent::class,
            'CallableArrayStaticMethodListener'
        );
    }
}

$subscriber = new EventSubscriber();

$provider = new Provider();

$provider->subscribe($subscriber);

$dispatcher = new EventDispatcher($provider);

$dispatcher->dispatch(new TestEvent());
```

### Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` or create a [Security Advisory](https://github.com/ghostwriter/event-dispatcher/security/advisories/new) instead of using the issue tracker.

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.
