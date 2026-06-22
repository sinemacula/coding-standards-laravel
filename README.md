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

Two standards ship; reference exactly one (each pulls in `SineMacula`, so it replaces it - don't reference both):

- **`SineMaculaLaravel`** - for applications. The full standard, including the role-based structure
  rules (placement and naming of controllers, models, providers, …) and the controller rules.
- **`SineMaculaLaravelPackage`** - for libraries and packages. The same standard with the app-skeleton
  rules (`Structure.*`, `Controllers.*`) excluded, since a package is organised by domain rather than
  Laravel's app directory layout. Composition only - it redefines nothing.

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
| `SineMaculaLaravel.Controllers.DisallowNonRestActions` | A controller's candidate actions (public, non-static instance methods) must be REST verbs or `__invoke`; statics, the constructor and framework overrides are auto-exempt. Mark a method `@non-rest-action` (a deliberate non-CRUD action) or `@utility` (not an action) to allow it. |
| `SineMaculaLaravel.Debug.DisallowDebugStatements` | No debug calls (`dd`, `dump`, `ray`, `var_dump`, `print_r`) in committed code. |
| `SineMaculaLaravel.Eloquent.DisallowLegacyAttributeAccessor` | No legacy `getXAttribute()` / `setXAttribute()` accessors - use `Attribute::make()`. |
| `SineMaculaLaravel.Services.DisallowHttpAbort` | No `abort()` / `abort_if` / `abort_unless` / `HttpException` in a service - throw a domain exception. |
| `SineMaculaLaravel.Structure.RequireBladeLocation` | A `*.blade.php` template must live under a `resources/views` (or module `Resources/views`) directory. |
| `SineMaculaLaravel.Structure.RequireRoleDirectory` | A class whose role is recognised by identity (what it extends/implements) must live under that role's directory - a controller under `Http/Controllers`; an entry-point provider may sit at the package root. |
| `SineMaculaLaravel.Structure.RequireRoleNaming` | A class is named for its role: controllers/providers/form-requests/resources/policies require a suffix, models forbid `Model`/`Entity`, and the rest (jobs, listeners, events, mailables, middleware, commands, casts, rules) stay bare. |
| `SineMaculaLaravel.Structure.RoutesLocation` | A `routes.php` file, if present, must sit at the root of an `Http` directory. |

#### Role-based structure

`RequireRoleNaming` and `RequireRoleDirectory` resolve a class's role by **identity first** - what it
`extends`, `implements`, `use`s or is attributed with - and fall back to its **location** (a concrete
class under a role directory, minus exempt sub-namespaces such as `Concerns`/`Support`/`Contracts`). A
class with neither is unconstrained, so genuine domain classes are never flagged.

The default role table is convention-correct for Laravel: it never requires a suffix the framework
leaves bare, and the idiomatic bare `User` model is honoured (the `Model` role's identity covers
`Authenticatable` and `Pivot`). Every list - `roleIdentities`, `roleLocations`, `requireSuffix`,
`forbidSuffix`, `exemptNamespaces`, `moduleRootRoles` - is a public sniff property a ruleset can
override. Identity is matched on the immediate base by short name, so a project's own intermediate
base (e.g. a `BaseController`) is supported by adding it to `roleIdentities`.

Opt a class out entirely with an `@role-exempt` docblock tag or a `#[NotARole]` attribute.

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
