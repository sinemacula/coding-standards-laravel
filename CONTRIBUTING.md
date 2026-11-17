# Contributing

Contributions are welcome via GitHub pull requests.

This package layers Laravel-specific rules on top of
[`sinemacula/coding-standards`](https://github.com/sinemacula/coding-standards). A rule that is not Laravel-specific
belongs in the base package instead.

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

```bash
composer check    # Lint via qlty (CodeSniffer, PHP-CS-Fixer)
composer analyse  # PHPStan over the package's own sniffs and rules
composer format   # Format via qlty
composer smells   # Duplication and complexity smells via qlty
```

### Testing

```bash
composer test            # PHPUnit sniff/rule suite
composer test:coverage   # With Clover coverage report (requires Xdebug)
composer test:mutation   # Mutation gate against origin/master
```

Single test file or method:

```bash
vendor/bin/phpunit SineMaculaLaravel/Tests/<Category>/<Name>SniffTest.php
vendor/bin/phpunit --filter <testMethod> SineMaculaLaravel/Tests/<Category>/<Name>SniffTest.php
```

### Standards

- New sniffs and PHPStan rules ship with tests; the mutation gate (`composer test:mutation`) is the enforced floor
- Sniffs are token-based and must not reference Laravel classes
- PHPStan rules name `Illuminate\...` classes as strings: `laravel/framework` is only a dev dependency
- Full type hints on all parameters and return types, with PHPDoc on all classes and methods

## Pull Requests

- Keep changes minimal and scoped to a single concern
- Do not change static-analysis or formatting configuration without prior discussion
- Include tests for new or changed behaviour
- `composer check`, `composer analyse`, `composer smells` and `composer test` pass

## Security

Report vulnerabilities privately, not in a public issue. See [SECURITY.md](SECURITY.md).

## License

By contributing, you agree that your contributions will be licensed under the [Apache License 2.0](LICENSE).
