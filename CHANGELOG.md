# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/)
and this project adheres to [Semantic Versioning](https://semver.org/).

## 5.0.0 - 2024-05-21

### Added

- Method `event()` was added to interface `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface`
- Method `listener()` was added to interface `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface`
- Method `listeners()` was added to interface `Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface`
- Method `throwable()` was added to interface `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface`
- Method `unbind()` was added to interface `Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface`
- Method `unsubscribe()` was added to interface `Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface`

### Changed

- Parameter 0 of `Ghostwriter\EventDispatcher\Interface\SubscriberInterface#__invoke()` changed name from `provider` to `listenerProvider`
- The number of required arguments for `Ghostwriter\EventDispatcher\ListenerProvider#__construct()` increased from 0 to 1
- The parameter `$event` of `Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface#dispatch()` changed from `Ghostwriter\EventDispatcher\Interface\EventInterface` to `object`
- The parameter `$reflector` of `Ghostwriter\EventDispatcher\ListenerProvider#__construct()` changed from `Ghostwriter\Container\Reflector` to a non-contravariant `Ghostwriter\Container\Interface\ContainerInterface`
- The return type of `Ghostwriter\EventDispatcher\EventDispatcher#dispatch()` changed from `Ghostwriter\EventDispatcher\Interface\EventInterface` to the non-covariant `object`
- The return type of `Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface#dispatch()` changed from `Ghostwriter\EventDispatcher\Interface\EventInterface` to `object`
- The return type of `Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface#dispatch()` changed from `Ghostwriter\EventDispatcher\Interface\EventInterface` to the non-covariant `object`

### Removed

- Class `Ghostwriter\EventDispatcher\Exception\EventMustImplementEventInterfaceException` has been deleted
- Class `Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException` has been deleted
- Class `Ghostwriter\EventDispatcher\Exception\MissingEventParameterException` has been deleted
- Class `Ghostwriter\EventDispatcher\Exception\MissingParameterTypeDeclarationException` has been deleted
- Class `Ghostwriter\EventDispatcher\Interface\EventInterface` has been deleted
- Class `Ghostwriter\EventDispatcher\Trait\EventTrait` has been deleted
- Constant `Ghostwriter\EventDispatcher\ListenerProvider::LISTENERS` was removed
- Constant `Ghostwriter\EventDispatcher\ListenerProvider::SUBSCRIBERS` was removed
- Method `Ghostwriter\EventDispatcher\Event\ErrorEvent#getEvent()` was removed
- Method `Ghostwriter\EventDispatcher\Event\ErrorEvent#getListener()` was removed
- Method `Ghostwriter\EventDispatcher\Event\ErrorEvent#getThrowable()` was removed
- Method `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface#getEvent()` was removed
- Method `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface#getListener()` was removed
- Method `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface#getThrowable()` was removed
- Method `Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface#getListenersForEvent()` was removed
- Method `Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface#listen()` was removed
- Method `Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface#remove()` was removed
- Method `Ghostwriter\EventDispatcher\ListenerProvider#getListenersForEvent()` was removed
- Method `Ghostwriter\EventDispatcher\ListenerProvider#hasListener()` was removed
- Method `Ghostwriter\EventDispatcher\ListenerProvider#listen()` was removed
- Method `Ghostwriter\EventDispatcher\ListenerProvider#remove()` was removed

## 4.0.0 - 2024-02-06

### Changed

- Rename Class `Ghostwriter\EventDispatcher\Interface\DispatcherInterface` to `Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface`
- Rename Class `Ghostwriter\EventDispatcher\Dispatcher` to `Ghostwriter\EventDispatcher\EventDispatcher`
- Parameter 0 of `Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface#remove()` changed name from `listenerId` to `listener`
- The return type of `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface#getListener()` changed from `mixed` to `string`
- The parameter `$listener` of `Ghostwriter\EventDispatcher\Event\ErrorEvent#__construct()` changed from `mixed` to `string`

## 3.0.0 - 2023-11-12

### Changed

- Drop `AbstractEvent ` in favor of `EventTrait` to avoid inheritance while still promoting reusability and reducing duplication.
- Drop `AnonymousFunctionListener`
- Drop `CallableArrayStaticMethodListener`
- Drop `CallableArrayInstanceListener`
- Drop `InvokableListener`
- Namespace changed `Ghostwriter\EventDispatcher\ErrorEvent` to `Ghostwriter\EventDispatcher\Event\ErrorEvent`
- Namespace changed `Ghostwriter\EventDispatcher\Interface\ErrorEventInterface` to `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface`
