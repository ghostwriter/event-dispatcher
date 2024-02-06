# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/)
and this project adheres to [Semantic Versioning](https://semver.org/).

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
