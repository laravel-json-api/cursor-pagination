# Change Log

All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## [3.0.0] - 2023-02-14

### Changed

- Upgrade to Laravel 10 and set minimum PHP version to 8.1.
- **BREAKING**: The schema's id field must now always be provided to the `CursorPagination::make()` method and/or
  constructor. I.e. use `CursorPagination::make($this->id())`

## [2.1.0] - 2023-01-24

### Added

- The cursor pagination implementation now supports id fields that are encoded. To use, pass your schema's id
  field to the cursor pagination class when returning it from the schema's `pagination()` method. For example:

```php
public function pagination(): CursorPagination
{
    return CursorPagination::make($this->id());
}
```

## [2.0.0] - 2022-02-09

### Added

- Package now supports Laravel 9.
- Package now supports PHP 8.1.
- Upgraded to v2 of the `laravel-json-api/eloquent` dependency.

### Changed

- Added return types to internal methods to remove deprecation messages in PHP 8.1.

## [1.0.0] - 2021-07-21

Initial release. This brought in the code from `laravel-json-api/eloquent:1.0.0-beta.6`, with the only changes being new
namespaces.
