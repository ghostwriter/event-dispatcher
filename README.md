# Event Dispatcher

[![Automation](https://github.com/ghostwriter/event-dispatcher/actions/workflows/automation.yml/badge.svg)](https://github.com/ghostwriter/event-dispatcher/actions/workflows/automation.yml)
[![PHP Version](https://badgen.net/packagist/php/ghostwriter/event-dispatcher?color=777BB4)](https://www.php.net/supported-versions)
[![Packagist Downloads](https://badgen.net/packagist/dt/ghostwriter/event-dispatcher?color=F28D1A)](https://packagist.org/packages/ghostwriter/event-dispatcher)
[![PayPal](https://img.shields.io/badge/paypal-@codepoet-0079C1?logo=paypal&logoColor=002991)](https://paypal.me/codepoet)
[![Sponsors via GitHub](https://img.shields.io/github/sponsors/ghostwriter?label=Sponsor+@ghostwriter/event-dispatcher&logo=GitHub+Sponsors)](https://github.com/sponsors/ghostwriter)

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

// Create an event class
final class ExampleEvent
{
}

// Create an Event Listener
final class ExampleEventListener
{
    public function __invoke(ExampleEvent $event): void
    {
        // Handle the event, e.g., print the event class name
        // echo $event::class;
    }
}

// Create a ListenerProvider
$listenerProvider = ListenerProvider::new(); // or new ListenerProvider(Container::getInstance())

// Bind the Listener to the Event
$listenerProvider->listen(ExampleEvent::class, ExampleEventListener::class);

// Create an EventDispatcher
$dispatcher = EventDispatcher::new($listenerProvider); // or new EventDispatcher($listenerProvider)

// Dispatch the Event.
$event = $dispatcher->dispatch(new ExampleEvent());

// Assert the Event is the same as the dispatched Event
assert($event instanceof ExampleEvent);
``` 

### Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` or create a [Security Advisory](https://github.com/ghostwriter/event-dispatcher/security/advisories/new) instead of using the issue tracker.

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.
