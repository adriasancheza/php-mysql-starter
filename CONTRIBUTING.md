# Contributing

Thanks for considering a contribution to php-mysql-starter.

## Getting set up

```bash
composer install
cp .env.example .env
php bin/migrate.php
```

## Before opening a pull request

```bash
composer test   # PHPUnit (integration tests skip automatically without MySQL)
composer stan   # PHPStan level 6
```

## Guidelines

- Keep dependencies minimal — this project is meant to run on shared
  hosting with plain `composer install`, no exotic extensions.
- Use `declare(strict_types=1)` and typed properties/parameters in all PHP
  files.
- Add or update tests for behavior changes.
- Keep commits focused and use conventional commit messages
  (`feat:`, `fix:`, `docs:`, `test:`, `chore:`...).

## Reporting issues

Open a GitHub issue with steps to reproduce, expected vs. actual behavior,
and your PHP/MySQL versions.
