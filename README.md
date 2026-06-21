# Coding Standards (Laravel)

[![Latest Stable Version](https://img.shields.io/packagist/v/sinemacula/coding-standards-laravel.svg)](https://packagist.org/packages/sinemacula/coding-standards-laravel)
[![Build Status](https://github.com/sinemacula/coding-standards-laravel/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/sinemacula/coding-standards-laravel/actions/workflows/tests.yml)
[![Maintainability](https://qlty.sh/gh/sinemacula/projects/coding-standards-laravel/maintainability.svg)](https://qlty.sh/gh/sinemacula/projects/coding-standards-laravel)
[![Code Coverage](https://qlty.sh/gh/sinemacula/projects/coding-standards-laravel/coverage.svg)](https://qlty.sh/gh/sinemacula/projects/coding-standards-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/sinemacula/coding-standards-laravel.svg)](https://packagist.org/packages/sinemacula/coding-standards-laravel)

Laravel-specific coding standards, static-analysis rules, and code-quality tooling for Sine Macula's Laravel repositories.

Install this **only in Laravel projects**. Framework-agnostic, language-wide standards live in
[`sinemacula/coding-standards`](https://github.com/sinemacula/coding-standards); this package adds the
Laravel-specific layer. Non-Laravel repos simply don't install it - that is how the Laravel rules stay
scoped to Laravel projects (no runtime framework detection).

## Installation

```bash
composer require --dev sinemacula/coding-standards-laravel
```

This brings `sinemacula/coding-standards` with it. You also need `squizlabs/php_codesniffer`,
`dealerdirect/phpcodesniffer-composer-installer`, and `slevomat/coding-standard` in your dev deps (as you
already do for the base standard).

## Usage

Wire it into the PHP tools through the same Qlty plugin setup you already use for the base standard
(`package_file = "composer.json"` with `package_filters = ["sinemacula", ...]` in `.qlty/qlty.toml` - the
`"sinemacula"` filter already matches this package, so Qlty installs it into the linter tool environments
automatically).

### PHPCS

Reference the `SineMaculaLaravel` standard (it pulls in `SineMacula`, so it replaces it - don't reference both):

```xml
<?xml version="1.0"?>
<ruleset name="Project">
    <rule ref="SineMaculaLaravel"/>
    <file>src</file>
    <file>tests</file>
</ruleset>
```

### PHPStan

The Laravel rules are auto-included via this package's `extra.phpstan.includes` (resolved by
`phpstan/extension-installer`), alongside the base config. Your project's `phpstan.neon` only needs its
own `level` / `paths`.

### PHP CS Fixer

If Laravel-specific fixer rules are present, reference the Laravel factory from your
`.php-cs-fixer.dist.php`; otherwise keep using the base `PhpCsFixerConfig::make()`.

## Rules

These are the Laravel-specific rules this package adds on top of the base `sinemacula/coding-standards`.
A deliberate exception can be bypassed with the native directive - `// phpcs:ignore <code>` for a sniff,
`@phpstan-ignore <identifier>` for a rule.

### PHPCS sniffs

| Sniff | Enforces |
|-------|----------|
| `SineMaculaLaravel.Architecture.DisallowServiceLocation` | No service location (`app()`, `resolve()`, `App::make()`) inside a class body - inject collaborators instead. |
| `SineMaculaLaravel.Configuration.DisallowEnvOutsideConfig` | `env()` only inside `config/` files; use `config()` everywhere else. |
| `SineMaculaLaravel.Controllers.DisallowDatabaseAccess` | No `DB::` facade or direct Eloquent model queries in a controller - read through a repository. |
| `SineMaculaLaravel.Controllers.DisallowInlineValidation` | No inline validation (`$request->validate()`, `Validator::make()`) in a controller - use a form request. |
| `SineMaculaLaravel.Controllers.DisallowNonRestActions` | A controller's public methods are limited to the REST actions (`index`/`show`/`store`/`update`/`destroy`/`create`/`edit`) or a single `__invoke`. |
| `SineMaculaLaravel.Debug.DisallowDebugStatements` | No debug calls (`dd`, `dump`, `ray`, `var_dump`, `print_r`) in committed code. |
| `SineMaculaLaravel.Eloquent.DisallowLegacyAttributeAccessor` | No legacy `getXAttribute()` / `setXAttribute()` accessors - use `Attribute::make()`. |
| `SineMaculaLaravel.Services.DisallowHttpAbort` | No `abort()` / `abort_if` / `abort_unless` / `HttpException` in a service - throw a domain exception. |
| `SineMaculaLaravel.Structure.RequireBladeLocation` | A `*.blade.php` template must live under a `resources/views` (or module `Resources/views`) directory. |
| `SineMaculaLaravel.Structure.RequireRoleDirectory` | A role class must live under its canonical directory (e.g. `*Controller` under `Http/Controllers`, `*Repository` under `Repositories`). |
| `SineMaculaLaravel.Structure.RequireRoleNaming` | A class under a role directory must carry that role's suffix. |
| `SineMaculaLaravel.Structure.RoutesLocation` | A `routes.php` file, if present, must sit at the root of an `Http` directory. |

### PHPStan rules

| Identifier | Enforces |
|------------|----------|
| `sineMaculaLaravel.castsProperty` | No `$casts` property on a model - use the `casts()` method. |
| `sineMaculaLaravel.datesProperty` | No `$dates` property on a model (deprecated) - cast dates via `casts()`. |
| `sineMaculaLaravel.massAssignment` | Every concrete model declares `$fillable` or `$guarded` explicitly. |
| `sineMaculaLaravel.relationshipReturnType` | A relationship method declares a return-type hint. |
| `sineMaculaLaravel.modelAttribute` | Prefer model attributes over their legacy forms: `$table`/`$hidden`/`$touches` → `#[Table]`/`#[Hidden]`/`#[Touches]`; `newFactory()`/`newCollection()`/`newEloquentBuilder()` → `#[UseFactory]`/`#[CollectedBy]`/`#[UseEloquentBuilder]`. |
| `sineMaculaLaravel.migrationMethods` | A migration defines both `up()` and `down()`. |
| `sineMaculaLaravel.formRequestRules` | A form request (under `Http\Requests`) defines a `rules()` method. |
| `sineMaculaLaravel.factoryTimestamps` | A factory `definition()` must not set `created_at` / `updated_at`. |

## Requirements

- PHP ^8.3

## Testing

```bash
composer test           # PHPUnit sniff/rule suite
composer test:coverage  # suite with Clover coverage output (requires Xdebug)
composer analyse        # PHPStan over the package's own sniffs and rules
composer check          # static analysis and lint via qlty
composer format         # format via qlty
composer smells         # duplication / complexity smells via qlty
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of notable changes.

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on branching, commits, code
quality, and pull requests.

## Security

If you discover a security vulnerability, please report it responsibly. See [SECURITY.md](SECURITY.md) for the
disclosure policy and contact details.

## License

Licensed under the [Apache License, Version 2.0](https://www.apache.org/licenses/LICENSE-2.0).
