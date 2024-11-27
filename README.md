<p align="center">
  <a href="" rel="noopener">
  <img width=200px height=200px src="https://placehold.jp/000000/ffffff/200x200.png?text=Testmode&css=%7B%22border-radius%22%3A%22%20100px%22%7D" alt="Testmode logo"></a>
</p>

<h1 align="center">Drupal module to modify existing site content and configurations while running tests.</h1>

<div align="center">

[![GitHub Issues](https://img.shields.io/github/issues/AlexSkrypnyk/testmode.svg)](https://github.com/AlexSkrypnyk/testmode/issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/AlexSkrypnyk/testmode.svg)](https://github.com/AlexSkrypnyk/testmode/pulls)
[![Test](https://github.com/AlexSkrypnyk/testmode/actions/workflows/test.yml/badge.svg)](https://github.com/AlexSkrypnyk/testmode/actions/workflows/test.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/AlexSkrypnyk/testmode)
![LICENSE](https://img.shields.io/github/license/AlexSkrypnyk/testmode)
![Renovate](https://img.shields.io/badge/renovate-enabled-green?logo=renovatebot)

![Drupal 10](https://img.shields.io/badge/Drupal-10-009CDE.svg)
![Drupal 11](https://img.shields.io/badge/Drupal-11-006AA9.svg)

</div>

---

This is a module to support testing, so it is not expected to be used in production (although, it adheres to Drupal coding standards and has good test coverage).

## Installation

```shell
composer require --dev drupal/testmode
```

## Use case

Running a Behat test on the site with existing content may result in
false-positives because of the live content being mixed with the test content.

Example: list of 3 featured articles. When the test creates 3 articles and makes
them featured, there may be existing featured articles that will confuse tests
resulting in a false-positive failure.

## How it works
1. When writing Behat tests, all test content items (nodes,
   terms, users) follow specific pattern. For example, node titles start with
   `[TEST] `.
2. A machine name of a view, which needs to be tested, is added to
   Testmode configuration form.
3. Behat test tagged with `@testmode` will put
   the site in test mode that will filter-out all items in the view that do not
   fit the pattern, leaving only content items created by the test.

## Maintenance / Development
Releases in GitHub are automatically pushed to http://drupal.org/project/testmode by CI.

## Issues
https://www.drupal.org/project/issues/testmode

## Local development

Provided that you have PHP installed locally, you can develop an extension using
the provided scripts.

### Build

Run the following commands to start inbuilt PHP
server locally and run the same commands as in CI, 
plus installing a site and Testmode automatically.
```shell
./.devtools/assemble.sh
./.devtools/start.sh
./.devtools/provision.sh
```
or `ahoy build` or `make build`.


### Code linting

Run tools individually (or `ahoy lint` to run all tools
if [Ahoy](https://github.com/ahoy-cli/ahoy) is installed or `make lint`) to lint your code
according to
the [Drupal coding standards](https://www.drupal.org/docs/develop/standards).

```
cd build

vendor/bin/phpcs
vendor/bin/phpstan
vendor/bin/rector --clear-cache --dry-run
vendor/bin/phpmd . text phpmd.xml
vendor/bin/twig-cs-fixer
```

- PHPCS config: [`phpcs.xml`](phpcs.xml)
- PHPStan config: [`phpstan.neon`](phpstan.neon)
- PHPMD config: [`phpmd.xml`](phpmd.xml)
- Rector config: [`rector.php`](rector.php)
- Twig CS Fixer config: [`.twig-cs-fixer.php`](.twig-cs-fixer.php)

### Tests

Run tests individually with `cd build && ./vendor/bin/phpunit` (`ahoy test` or `make test`) to run all test for
Testmode.

### Browsing SQLite database

To browse the contents of created SQLite database
(located at `/tmp/site_testmode.sqlite`),
use [DB Browser for SQLite](https://sqlitebrowser.org/).

---
_This repository was created using the [Drupal Extension Scaffold](https://github.com/AlexSkrypnyk/drupal_extension_scaffold) project template_

