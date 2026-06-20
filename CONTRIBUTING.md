# Contributing

Contributions are welcome via GitHub pull requests. This guide covers the expectations for working on this package.

This package layers Laravel-specific rules on top of the framework-agnostic
[`sinemacula/coding-standards`](https://github.com/sinemacula/coding-standards). Keep rules here non-overlapping with
the base: the base owns the framework-agnostic rules, this package owns only the Laravel-specific ones.

## Requirements

- PHP 8.3+
- Composer 2

## Getting Started

```bash
git clone git@github.com:sinemacula/coding-standards-laravel.git
cd coding-standards-laravel
composer install
```

## Development Workflow

### Branching

Branch from `master` using the appropriate prefix:

| Prefix      | Purpose                          |
|-------------|----------------------------------|
| `feature/`  | New functionality                |
| `bugfix/`   | Bug fixes                        |
| `hotfix/`   | Urgent production fixes          |
| `refactor/` | Refactoring without new features |
| `chore/`    | Tooling, CI, dependencies        |

### Commits

This project uses [Conventional Commits](https://www.conventionalcommits.org/). Prefix your commit messages accordingly:

```text
feat(phpcs): disallow inline validation in controllers
feat(phpstan): require the casts() method over the $casts property
fix(phpcs): handle nested closures in the service-location sniff
chore: update qlty configuration
```

### Code Quality

All code must pass static analysis before submission:

```bash
composer check    # Static analysis and lint checks via qlty (PHPStan, PHP-CS-Fixer, CodeSniffer)
composer format   # Format the codebase via qlty
composer smells   # Advisory code smells (duplication, complexity)
```

### Testing

Run the full test suite before submitting:

```bash
composer test            # Run the PHPUnit sniff/rule test suite
composer test:coverage   # With Clover coverage report (requires Xdebug)
```

Single test file or method:

```bash
vendor/bin/phpunit SineMaculaLaravel/Tests/Sniffs/<Category>/<Name>SniffTest.php
vendor/bin/phpunit --filter testDetectsViolations SineMaculaLaravel/Tests/Sniffs/<Category>/<Name>SniffTest.php
```

### Standards

- New sniffs and PHPStan rules ship with tests and maintain 100% line coverage
- Sniffs are token-based and must not reference Laravel classes (they see tokens, not types)
- PHPStan rules are pure AST/reflection; this package does not require `laravel/framework`, so rules cannot reflect on
  `Illuminate\...` types directly
- Full type hints on all method parameters and return types, with PHPDoc on all classes and methods

## Pull Requests

- Keep changes minimal and scoped to a single concern
- Do not change static-analysis or formatting configuration without prior discussion
- Include tests for new or changed behaviour
- Ensure `composer check` and `composer test` pass

## Security

If you discover a security vulnerability, please report it directly to Sine Macula rather than opening a public issue.
See [SECURITY.md](SECURITY.md) for details.

## License

By contributing, you agree that your contributions will be licensed under the [Apache License 2.0](LICENSE).
