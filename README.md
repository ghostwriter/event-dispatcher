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

### Star ⭐️ this repo if you find it useful

You can also star (🌟) this repo to find it easier later.

### Usage

Registering and dispatching an Event Listener.

```php
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\ListenerProvider;

final class ExampleEvent
{
}

final class ExampleEventListener
{
    public function __invoke(ExampleEvent $event): void
    {
        // ... print $event::class;
    }
}

$provider = ListenerProvider::new();

$provider->listen(ExampleEvent::class, ExampleEventListener::class);

$dispatcher = EventDispatcher::new($provider);

$event = $dispatcher->dispatch(new ExampleEvent());

assert($event instanceof ExampleEvent);
```

### Event Subscriber

Registering an Event Subscriber.

```php
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\EventDispatcher\Interface\SubscriberInterface;
use Override;

final class EventSubscriber implements SubscriberInterface {
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ListenerProviderInterface $provider): void
    {
        // InvokableListener '::__invoke'
        $provider->listen(
            TestEvent::class, 
            TestEventListener::class,
        );
    }
}

$provider = ListenerProvider::new();

$provider->subscribe(EventSubscriber::class);

$dispatcher = EventDispatcher::new($provider);

$event = $dispatcher->dispatch(new TestEvent());

assert($event instanceof TestEvent);
```

### Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` or create a [Security Advisory](https://github.com/ghostwriter/event-dispatcher/security/advisories/new) instead of using the issue tracker.

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.
