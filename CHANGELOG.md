# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/)
and this project adheres to [Semantic Versioning](https://semver.org/).

## 3.0.0 - 2023-11-12

### Changed

- Drop `AbstractEvent ` in favor of `EventTrait` to avoid inheritance while still promoting reusability and reducing duplication.
- Drop `AnonymousFunctionListener`
- Drop `CallableArrayStaticMethodListener`
- Drop `CallableArrayInstanceListener`
- Drop `InvokableListener`
- Namespace changed `Ghostwriter\EventDispatcher\ErrorEvent` to `Ghostwriter\EventDispatcher\Event\ErrorEvent`
- Namespace changed `Ghostwriter\EventDispatcher\Interface\ErrorEventInterface` to `Ghostwriter\EventDispatcher\Interface\Event\ErrorEventInterface`
