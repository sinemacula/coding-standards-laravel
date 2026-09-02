# Coding Standards (Laravel)

[![Latest Stable Version](https://img.shields.io/packagist/v/sinemacula/coding-standards-laravel.svg)](https://packagist.org/packages/sinemacula/coding-standards-laravel)
[![Build Status](https://github.com/sinemacula/coding-standards-laravel/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/sinemacula/coding-standards-laravel/actions/workflows/tests.yml)
[![Quality Gates](https://github.com/sinemacula/coding-standards-laravel/actions/workflows/quality-gates.yml/badge.svg?branch=master)](https://github.com/sinemacula/coding-standards-laravel/actions/workflows/quality-gates.yml)
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
own `paths`.

Do not set `level`. Analysis runs through qlty, whose phpstan driver passes `--level=9` on the command
line, and a command-line level overrides the config file outright - so a level set here does nothing
except mislead whoever reads it next.

### PHP CS Fixer

If Laravel-specific fixer rules are present, reference the Laravel factory from your
`.php-cs-fixer.dist.php`; otherwise keep using the base `PhpCsFixerConfig::make()`.

## Rules

These are the Laravel-specific rules this package adds on top of the base `sinemacula/coding-standards`.
A deliberate exception can be bypassed with the native directive - `// phpcs:ignore <code>` for a sniff,
`@phpstan-ignore <identifier>` for a rule.

### PHPCS sniffs

| Sniff                                                        | Enforces                                                                                                                                          |
|--------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------|
| `SineMaculaLaravel.Architecture.DisallowServiceLocation`     | No service location (`app()`, `resolve()`, `App::make()`) in a class body - inject collaborators instead.                                         |
| `SineMaculaLaravel.Configuration.DisallowEnvOutsideConfig`   | `env()` only inside `config/` files (test code exempt); use `config()` everywhere else.                                                           |
| `SineMaculaLaravel.Controllers.DisallowDatabaseAccess`       | No `DB::` facade or direct Eloquent model queries in a controller - read through a repository.                                                    |
| `SineMaculaLaravel.Controllers.DisallowInlineValidation`     | No inline validation (`$request->validate()`, `Validator::make()`) in a controller - use a form request.                                          |
| `SineMaculaLaravel.Controllers.DisallowNonRestActions`       | A controller's actions are REST verbs or `__invoke`; `@non-rest-action` or `@utility` allows an exception.                                        |
| `SineMaculaLaravel.Debug.DisallowDebugStatements`            | No debug calls (`dd`, `dump`, `ray`, `var_dump`, `print_r`); the string-returning `print_r($value, true)` is allowed.                             |
| `SineMaculaLaravel.Eloquent.DisallowLegacyAttributeAccessor` | No legacy `getXAttribute()` / `setXAttribute()` accessors on an Eloquent model - use `Attribute::make()`.                                         |
| `SineMaculaLaravel.Routing.CollapseResourceRoutes`           | Routes for one controller that together form a whole resource collapse into `Route::apiResource(...)->only([...])`.                               |
| `SineMaculaLaravel.Services.DisallowHttpAbort`               | No `abort()` / `abort_if` / `abort_unless` / `HttpException` in a service - throw a domain exception.                                             |
| `SineMaculaLaravel.Structure.RequireBladeLocation`           | A `*.blade.php` template must live under a `resources/views` (or module `Resources/views`) directory.                                             |
| `SineMaculaLaravel.Structure.RequireRoleDirectory`           | A class whose role is recognised by identity lives under that role's directory - a controller under `Http/Controllers`.                           |
| `SineMaculaLaravel.Structure.RequireRoleNaming`              | A class is named for its role - a required suffix for controllers, providers, form requests, resources and policies.                              |
| `SineMaculaLaravel.Structure.RoutesLocation`                 | A `routes.php` file, if present, must sit at the root of an `Http` directory.                                                                     |
| `SineMaculaLaravel.TypeHints.PropertyTypeHint`               | A class property declares a native type - except the `magicProperties` set, or a property marked `@untypeable`.                                   |
| `SineMaculaLaravel.TypeHints.ParameterTypeHint`              | A parameter declares a native type - except where a parent fixes the signature (`#[\Override]`, a trait method).                                  |
| `SineMaculaLaravel.TypeHints.ReturnTypeHint`                 | A function, method or closure declares a native return type - except constructors/destructors/clone handlers and methods carrying `#[\Override]`. |

#### Service location

The rule targets production code. Exempt are test files; container-wiring classes, recognised by a
`Providers` namespace, a `Provider`/`Registrar` suffix or a `ServiceProvider`/`Registrar` base; classes the
framework constructs itself and so leaves no injection point (the `uninjectableBaseClasses` set, defaulting
to Eloquent's `Factory`); and resolution of a runtime variable, `app($class)`, which injection cannot
replace.

An entry in `uninjectableBaseClasses` containing a `\` is matched against the parent resolved through the
file's imports, so an unrelated `Illuminate\View\Factory` is not exempted. A bare entry matches the short
name, which is how a project adds an intermediate base of its own.

#### Debug statements

Only the return form is left alone: `print_r($value, false)` and the bare `print_r($value)` are still
flagged, and `var_dump` has no return form at all.

#### Resource routes

`CollapseResourceRoutes` is deliberately conservative. Any extra or nested route, a renamed action, an
inconsistent parameter, or a per-route modifier beyond the URI-constraint family leaves the group
untouched, and routes are only grouped within their enclosing `group()` closure.

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
base (e.g. a `BaseController`) is supported by adding it to `roleIdentities`. An identity entry
containing `\` instead matches the qualified name resolved through the file's imports, which keeps
same-named traits distinct: the `Job` role's `Bus\Dispatchable` entry identifies a sync job without
ever matching the Events `Dispatchable` an idiomatic event uses. Test classes - by name, test-case
base, or a `tests/` path - play no role at all, so a test mirroring a role namespace
(`Tests\Unit\Policies\...`) is never constrained.

Opt a class out entirely with an `@role-exempt` docblock tag or a `#[NotARole]` attribute (matched
by short name; `SineMacula\CodingStandardsLaravel\Attributes\NotARole` ships so the reference also
resolves under static analysis). Both hatches work with other attributes present on the class.

#### Type hints

The `TypeHints.*` sniffs replace the base standard's Slevomat native-type requirements with
Laravel-aware equivalents (the base Slevomat `MissingNativeTypeHint` / `MissingAnyTypeHint` codes are
excluded; the traversable-specification checks are kept). They are needed because the Slevomat sniffs
are inheritance-blind: PHP forbids typing a property that overrides an untyped parent (`$table`,
`$fillable`, …) or changing an inherited method signature, so the original rules force types that
fatal at class load. Native types are still required everywhere else. The exempt property set is the
configurable `magicProperties` list on `PropertyTypeHint`, and an overriding method opts its
signature out with the native `#[\Override]` attribute. A non-private trait method is also exempt
from the parameter requirement, since its effective parent is whatever the consuming class extends
and a token sniff cannot resolve that.

`magicProperties` covers the framework base classes an application extends - models, factories,
commands, migrations, API resources, middleware, the HTTP/console kernels, service providers and the
exception handler. It cannot know about third-party or application base classes, so a property whose
untyped parent declaration is out of the list's reach is marked `@untypeable` on its own docblock:

```php
/**
 * The vendor base class declares this untyped, so PHP forbids typing it here.
 *
 * @untypeable
 */
protected $view;
```

#### Readonly properties

The base standard requires every public property to be `readonly`. An Eloquent model is exempt: it
overrides the framework's public magic properties (`$timestamps`, `$incrementing`, …), which are
declared non-readonly on the base class, and PHP forbids making an inherited property readonly. This
standard sets the base sniff's `ignoredParentClasses` to the model bases (`Model`, `Authenticatable`,
`Pivot`), matched by the immediate parent name as written.

### PHPStan rules

| Identifier                                     | Enforces                                                                                                      |
|------------------------------------------------|---------------------------------------------------------------------------------------------------------------|
| `sineMaculaLaravel.castsProperty`              | No `$casts` property on an Eloquent model - use the `casts()` method.                                         |
| `sineMaculaLaravel.datesProperty`              | No `$dates` property on a model (deprecated) - cast dates via `casts()`.                                      |
| `sineMaculaLaravel.massAssignment`             | Every concrete production model declares mass assignment via `$fillable`/`$guarded` or the attribute form.    |
| `sineMaculaLaravel.fillableCasts`              | Every `$fillable` entry on a model declares a matching cast, documenting each settable field's type.          |
| `sineMaculaLaravel.relationshipReturnType`     | A relationship method declares a return-type hint.                                                            |
| `sineMaculaLaravel.modelAttribute`             | Prefer a model attribute over its legacy property or method form, for the attributes a project enables.       |
| `sineMaculaLaravel.modelAttributeLaggingFloor` | The legacy form of an attribute the project already resolves a Laravel version for, while its floor does not. |
| `sineMaculaLaravel.foreignIdFor`               | A foreign key column in a migration is declared from its model - `foreignIdFor(Organization::class)`.         |
| `sineMaculaLaravel.migrationMethods`           | A migration defines both `up()` and `down()`.                                                                 |
| `sineMaculaLaravel.schemaNaming`               | Table and column names in a migration use snake_case; digits are allowed, only casing is enforced.            |
| `sineMaculaLaravel.formRequestRules`           | A form request (under `Http\Requests`) defines a `rules()` method; classes declared in tests are exempt.      |
| `sineMaculaLaravel.factoryTimestamps`          | A factory `definition()` must not set `created_at` / `updated_at`.                                            |
| `sineMaculaLaravel.resourceFieldNaming`        | Field keys in a resource's `toArray()` result use snake_case, nested arrays included.                         |

#### Model attributes and the version gate

`modelAttribute` mandates the attributes a project enables - by default `#[Table]`, `#[Fillable]` and
`#[Hidden]`, configurable through `sineMaculaLaravel.modelAttributes`. Those landed in Laravel 13.2, so they
are enforced only once the project's floor reaches it, taken from `sineMaculaLaravel.minLaravelVersion` or
detected from the nearest `composer.json`.

Where the floor sits below 13.2 but `composer.lock` already resolves above it, the legacy form is reported
under `modelAttributeLaggingFloor` instead. The attribute cannot be adopted until the floor moves, but the
gap stays visible rather than arriving as a bulk migration the day it does. A package that supports older
versions deliberately can ignore that identifier and keep the enforcement one.

#### What the model and migration rules inspect

`fillableCasts` runs only on a concrete model directly extending `Model`/`Authenticatable`/`Pivot`, and
skips a `$fillable` or `casts()` that is not a plain enumerable literal. `schemaNaming` and
`resourceFieldNaming` inspect string literals only, so computed keys and dynamic names are left alone.
`foreignIdFor` flags the longhand `foreignId`/`foreignUuid`/`foreignUlid` calls whose literal column name
ends in `_id`; bypass a column pointing at a table this service does not own with `@phpstan-ignore
sineMaculaLaravel.foreignIdFor`. Classes declared in tests are exempt throughout.

#### Builder calls under the strict rules

Larastan reflects every method forwarded onto an Eloquent builder - the query methods and any
`#[Scope]` - as a static method, so `phpstan-strict-rules` reads ordinary query code as a dynamic
call to a static one:

```text
Dynamic call to static method Illuminate\Database\Eloquent\Builder<App\Models\Credential>::whereNull().
```

That lands on essentially every query in a project, so this standard ignores it, scoped to the
framework builders rather than turning the rule off - a genuine dynamic call to a static method
elsewhere is still reported.

A model pointing at its own builder with `#[UseEloquentBuilder]` is reported against **that** class
instead, which no shipped pattern can name. Add the matching entry to the project's own config:

```neon
parameters:
    ignoreErrors:
        -
            identifier: staticMethod.dynamicCall
            message: '#^Dynamic call to static method App\\Builders\\#'
```

#### Where model columns come from

A model's columns are read from its migrations, and everything that rests on them - the property
checks, the cast types, the `model property of ...` parameter types - is only as good as that scan.
Left unset, larastan resolves the path from the booted application rather than from the project being
analysed, and an isolated runner can boot it somewhere else; the two then disagree, no migration is
found, and the checks quietly stop covering anything. The standard names the conventional path so it
is pinned to the project:

```neon
parameters:
    databaseMigrationsPath:
        - %currentWorkingDirectory%/database/migrations
```

Replace it where migrations live elsewhere, or are kept per module - the entries accept globs, and
`databaseMigrationsPath!:` replaces rather than appends:

```neon
parameters:
    databaseMigrationsPath!:
        - %currentWorkingDirectory%/database/migrations
        - %currentWorkingDirectory%/app/*/Database/Migrations
```

If the scan finds nothing the failure is loud rather than silent: with no columns to resolve against,
every model property is reported as undefined.

## Requirements

- PHP ^8.3

## Testing

```bash
composer test                # PHPUnit sniff/rule suite
composer test:coverage       # suite with Clover coverage output (requires Xdebug)
composer test:mutation       # Infection mutation gate (min MSI 90)
composer test:mutation:full  # full mutation suite without thresholds
composer analyse             # PHPStan over the package's own sniffs and rules
composer check               # static analysis and lint via qlty
composer format              # format via qlty
composer smells              # duplication / complexity smells via qlty
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
