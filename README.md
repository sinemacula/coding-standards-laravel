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

## License

Licensed under the [Apache License, Version 2.0](https://www.apache.org/licenses/LICENSE-2.0).
