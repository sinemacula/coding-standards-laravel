# Build handoff: `sinemacula/coding-standards-laravel`

This repo is **scaffolded but not yet populated**. Its job is to hold the **Laravel-specific** linting
rules for Sine Macula, layered on top of the framework-agnostic
[`sinemacula/coding-standards`](https://github.com/sinemacula/coding-standards) (installed in `vendor/`).
This document is the spec for building it out. Work through the candidate list near the bottom with the
user, in small chunks, exactly as the base repo was built.

---

## 1. Why this is a separate package (architecture)

The base `coding-standards` repo is deliberately **framework-agnostic** (PHP + TypeScript, no framework
coupling). Laravel-specific rules live here instead so the base never accretes framework code, never pays
a runtime "is this Laravel?" detection cost, and never breaks when Laravel's internals change.

Conditionality is achieved by **installation, not detection**: Laravel projects `composer require --dev`
this package and reference its standard; non-Laravel projects don't. There is **no `class_exists('Illuminate\…')`
auto-detection** anywhere - don't add any.

This package **does not** ship JS/markdown/yaml configs and is **not** a Qlty *source* (no `source.toml`).
It is a plain Composer package of `type: phpcodesniffer-standard` that ships PHP sniffs + PHPStan rules
(+ optionally cs-fixer rules).

## 2. How consumers actually run these rules (Qlty - verified)

This was researched and confirmed against the live `data-normalizer-php` consumer. Consumers run **all**
PHP linting through Qlty (`qlty check` / `qlty fmt`), not `vendor/bin`. Qlty runs each tool (phpcs,
phpstan, php-cs-fixer) from its **own isolated install**, and makes the custom sniff/rule classes
available by reading the consumer's `composer.json` via the plugin's `package_file = "composer.json"` +
`package_filters`, then `composer install`ing the matched packages **into the tool's own vendor dir**.

Consequences that constrain how you build rules here:

- **This package must be public on Packagist.** Qlty's extension mechanism cannot install private/Git/local
  packages. Non-negotiable.
- **Ship rules as proper package classes with this package's own PSR-4 autoload** - never rely on a
  consumer project's autoload (Qlty strips the consumer's `autoload`/`autoload-dev`). Sniffs live under the
  `SineMaculaLaravel\Sniffs\` (phpcs-autoloaded) tree; PHPStan rules under `SineMacula\CodingStandardsLaravel\`
  (Composer PSR-4 → `src/`).
- **`package_filters = ["sinemacula"]` is a substring match**, so it already matches
  `sinemacula/coding-standards-laravel`. A Laravel consumer that adds this package to `require-dev` needs
  **no qlty.toml change** - Qlty installs it into the phpcs/phpstan/cs-fixer tool envs automatically, and
  `dealerdirect/phpcodesniffer-composer-installer` (already filtered in) auto-discovers the
  `SineMaculaLaravel` standard.
- Keep this package's sniffs **non-overlapping** with the base (base owns the agnostic rules; here add only
  Laravel-specific ones). They're separate standards referenced explicitly, so there's no collision.

**Before relying on any new rule in CI, validate empirically**: in a throwaway Laravel-ish fixture repo,
add this package, run `qlty config validate` + an actual `qlty check`, and confirm a Laravel rule fires.

## 3. Repo layout

```
SineMaculaLaravel/
  ruleset.xml                 # the `SineMaculaLaravel` standard; <rule ref="SineMacula"/> + auto-discovered local sniffs
  Sniffs/<Category>/<Name>Sniff.php      # namespace SineMaculaLaravel\Sniffs\<Category>
  Tests/
    bootstrap.php             # PHPUnit bootstrap (php-parser-before-phpcs token fix)
    AbstractSniffTestCase.php # convention-based sniff test base
    <Category>/<Name>SniffTest.php  +  <Name>.inc   # one test + fixture per sniff
src/
  PHPStan/Rules/<Name>Rule.php           # namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules
php/
  phpstan-laravel.neon        # registers the PHPStan rules (auto-included in consumers via extra.phpstan.includes)
phpunit.xml.dist              # coverage scoped to SineMaculaLaravel/Sniffs + src
.qlty/qlty.toml               # this repo's own qlty self-linting (radarlint/trufflehog/editorconfig)
.github/workflows/tests.yml   # PHP 8.3/8.4, xdebug coverage, qlty coverage upload
```

A **complete worked example** already exists and passes at 100% coverage:
`SineMaculaLaravel/Sniffs/Architecture/DisallowServiceLocationSniff.php` (+ its test + `.inc`). Copy its
shape for new sniffs.

## 4. How to add a phpcs sniff

1. Create `SineMaculaLaravel/Sniffs/<Category>/<Name>Sniff.php`, namespace
   `SineMaculaLaravel\Sniffs\<Category>`, `implements PHP_CodeSniffer\Sniffs\Sniff`. Token-based only -
   **no Laravel classes referenced** (sniffs see tokens, not types). `@author`/`@copyright` + method
   docblocks, single-line property/const docblocks (no redundant `@var` on typed scalars).
2. Create `SineMaculaLaravel/Tests/<Category>/<Name>SniffTest.php` extending `AbstractSniffTestCase`, with
   `<Name>.inc` fixture beside it. The base derives the sniff path/code from the test class by convention;
   a test is just `assertErrorsOnLines('<Name>.inc', [<lines>])`. Configurable sniffs: override
   `sniffProperties()` to set lowered limits.
3. `composer test` must be green; **maintain 100% line coverage** (`php -d pcov.enabled=0 -d
   xdebug.mode=coverage vendor/bin/phpunit --coverage-text`). For genuinely-unreachable defensive guards
   (e.g. a missing `parenthesis_opener` on a valid `T_FUNCTION`), mark the `return;` with
   `// @codeCoverageIgnore` - same discipline as the base.

## 5. How to add a PHPStan rule

The base repo's `vendor/sinemacula/coding-standards/src/PHPStan/Rules/NoMutableStaticPropertyRule.php` +
its `SineMacula/Tests/PHPStan/` test are the reference template - read them.

1. Create `src/PHPStan/Rules/<Name>Rule.php`, namespace `SineMacula\CodingStandardsLaravel\PHPStan\Rules`,
   `implements PHPStan\Rules\Rule`, with a `sineMaculaLaravel.<id>` error identifier.
2. Register it under `rules:` in `php/phpstan-laravel.neon`.
3. Test with `PHPStan\Testing\RuleTestCase` under `SineMaculaLaravel/Tests/PHPStan/` (use `.inc` fixtures;
   `analyse([...], [[message, line]])`). Keep it in the PHPUnit suite + coverage.
4. **Laravel-type-aware rules:** this package does **not** require `laravel/framework`, so a rule cannot
   reflect on `Illuminate\…\Model` etc. directly. Two options: (a) write a pure-AST rule (e.g. "`$casts`
   property forbidden", "`env()` outside `config/`") that needs no Laravel types - prefer these; or (b) for
   rules genuinely needing Eloquent/query-builder type info, that is **larastan territory** - flag it to
   the user; it likely belongs as a larastan-dependent rule with stub fixtures, and may be out of scope.

## 6. cs-fixer (only if needed)

Most Laravel candidates are phpcs/phpstan. If a Laravel auto-fix rule is wanted, add a factory
`src/PhpCsFixerConfig.php` (namespace `SineMacula\CodingStandardsLaravel`) that wraps the base
`SineMacula\CodingStandards\PhpCsFixerConfig::make()` and merges Laravel rules; consumers reference it from
their `.php-cs-fixer.dist.php`. Verify it under Qlty (the base's factory pattern is proven to load via
`package_filters`).

## 7. Quality bar & workflow (mirror the base)

- Branch per chunk, open a PR, keep CI green (8.3 + 8.4). Conventional-commit messages (`feat(phpcs): …`,
  `feat(phpstan): …`). **Never use em/en dashes** anywhere (user-wide rule) - use a hyphen.
- **100% line coverage** is the standard; the example proves the harness end-to-end.
- **Work the candidate list in chunks of ~3 with the user**, exactly like the base build: for each, decide
  build (phpcs/phpstan/larastan) vs review-only, look the tooling up where relevant, then implement the
  approved ones. Most `[review]` items below are not reliably automatable - confirm and leave them.
- This file (`CLAUDE.md`) can be trimmed to lean project instructions once the backlog is worked.

---

## 8. Laravel lint candidates (the backlog)

Tags: `[phpcs:custom]` token-based sniff · `[phpstan:custom]` AST/reflection rule (no Laravel types) ·
`[larastan]` needs Eloquent/query type awareness · `[phpcs]` existing/simple sniff · `[slevomat]` config ·
`[deptrac]` layer boundaries · `[review]` not reliably automatable.

> **Built already (reference example):** `[phpcs:custom]` No `app()` / `resolve()` / `App::make()`
> service location inside a class body -> `SineMaculaLaravel.Architecture.DisallowServiceLocation`. This
> also satisfies the Services-section "collaborators constructor-injected, not resolved via facades/`app()`"
> item. Review/refine with the user as the first chunk.

### Eloquent / Models
- Use the `casts()` method, never the `$casts` property. `[phpstan:custom]`
- `$dates` is forbidden (deprecated) - cast dates via `casts()`. `[phpstan:custom]`
- Mass assignment declared explicitly (`$fillable` or `$guarded`). `[phpstan:custom]`
- Relationship methods carry a return type hint (`: HasMany`, …). `[phpstan:custom]`
- Accessors/mutators via `Attribute::make()`, not legacy `getXAttribute()`/`setXAttribute()`. `[phpcs:custom]`
- An accessor listed in `$appends` must not query the DB (N+1). `[larastan]`
- `$table` declared explicitly when the model name does not map to the table. `[review]`
- Inbound attribute values normalised via the shared `Normalizer` in mutators. `[review]`
- No business logic on the model beyond data/casts/relationships/scopes/invariants. `[review]`

### Controllers & HTTP
- Controllers extend the base controller (`AuthorizedController`), not Laravel's `Controller`. `[phpstan:custom]`
- Controllers declare the `RESOURCE_MODEL` constant. `[phpstan:custom]`
- Controllers must not use raw Eloquent / `DB::`; read via repositories, mutate via services. `[larastan]`
- Responses returned via the `respondWith*` helpers, not built by hand. `[review]`
- No inline validation (`$request->validate()`, `Validator::make()`) in controllers. `[phpcs:custom]`
- Controller actions limited to the canonical REST set or a single `__invoke`. `[phpstan:custom]`
- Authorisation via policy/gate, not ad-hoc role checks. `[review]`

### Form Requests & validation
- Form requests extend the toolkit `FormRequest` (not Laravel's base). `[phpstan:custom]`
- Each form request defines `rules()`; `authorize()` only where not covered by controller policy. `[phpstan:custom]`
- All request-shape validation lives in a form request, never inline in a controller/service. `[phpcs:custom]`

### API Resources
- API responses through an `ApiResource`/`JsonResource`, never a raw model, array, or `toArray()`. `[review]`
- Resource schema declared in `schema()` via the `Field::`/`Relation::` builders. `[review]`
- Resources declare `@mixin <Model>` + a `RESOURCE_TYPE` constant; default fields in `$default`. `[phpstan:custom]`

### Services
- Business logic lives in services (use-case layer); services may freely access the DB. `[review]`
- Services extend the `Service` / `AuthenticatedService` base. `[phpstan:custom]`
- Repository access via the `HasRepositories` trait, with a `@method` annotation per accessor. `[review]`
- Services must not return framework `Response` / `JsonResource` objects. `[phpstan:custom]`
- Collaborators constructor-injected, not resolved via facades/`app()` in bodies. `[phpcs:custom]` **(done - see example)**
- Vendor/SDK exceptions mapped to domain exceptions - never leaked out of a service. `[review]`
- Services throw exceptions, never `abort()` / `HttpException`. `[phpcs:custom]`

### Repositories
- Extend `ApiRepository` and implement `model(): class-string`. `[phpstan:custom]`
- Query scopes named `scope*`, built via `addScope(Closure)`. `[phpcs:custom]`
- Reusable scoping lives in `Has*` repository traits. `[review]`
- Complex filter logic lives in `*Criteria` objects extending `ApiCriteria`. `[phpcs:custom]`
- Data access only - no business decisions (no branch-and-throw on a domain rule). `[review]`
- Default eager-loading declared via `$with` where collections are returned (N+1). `[review]`

### Events, Observers, Listeners
- Observers that dispatch events implement `ShouldHandleEventsAfterCommit`. `[review]`
- An observer `updated()` guards on `getChanges()` before acting. `[review]`
- Listeners dispatch via the after-commit helper, not bare `Event::dispatch()`. `[review]`
- Events are immutable (`readonly`) data carriers. `[phpcs:custom]`
- Observers (suffixed `Observer`) hold side-effects, not business logic. `[review]`

### Jobs & queues
- Jobs implement `ShouldQueue`. `[phpstan:custom]`
- Jobs that must not double-run implement `ShouldBeUnique` with `uniqueId()` + `$uniqueFor`. `[phpstan:custom]`
- Jobs dispatched after commit (`ShouldQueueAfterCommit` / `dispatch()->afterCommit()`). `[review]`
- Job constructors take identifiers or rely on `SerializesModels` - no bare hydrated models. `[phpstan:custom]`

### Enums
- Pure enums implement `PureEnumInterface` + use the `PureEnumHelper` trait. `[phpstan:custom]`
- Enums are backed when the value is persisted or serialised. `[review]`
- Closed sets / status fields use an enum, not loose class constants or magic strings. `[review]`

### Exceptions
- Domain exceptions extend a base + implement the package's marker exception interface. `[phpstan:custom]`
- Static factory constructors (`from*`) where construction maps from another type. `[review]`
- Promoted, `readonly` context properties; the human message built in the constructor. `[review]`

### Authorization
- A Policy exists for every API-exposed model. `[review]`
- Policies use the `before()` pattern for internal-user/permission short-circuits. `[review]`

### Config, structure & runtime
- `env()` only inside `config/` files; everywhere else use `config()`. `[phpstan:custom]`
- Config files use pipe-banner section comments (80 chars wide). `[phpcs:custom]`
- No debug statements (`dd`, `dump`, `ray`, `var_dump`, `print_r`) in committed code. `[phpcs]`
- Module code lives under `modules/{Module}/{Layer}` with the namespace matching the path. `[slevomat]`
- The domain/use-case layer imports no `Illuminate\Http\Request` or Eloquent models. `[deptrac]`
- Routes defined in the module's route file, not registered ad-hoc. `[review]`
- Scheduled commands use `onOneServer()`; critical ones add `withoutOverlapping()`. `[review]`
