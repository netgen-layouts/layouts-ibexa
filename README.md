# Netgen Layouts & Ibexa CMS integration

## Installation instructions

### Use Composer to install the integration

Run the following command to install Netgen Layouts & Ibexa CMS integration:

```bash
composer require netgen/layouts-ibexa
```

Symfony Flex will automatically enable the bundle and import the routes.

### Install frontend assets

Run the following command to install frontend assets:

```bash
php bin/console assets:install --symlink --relative
```

## Running tests

Running tests requires that you have complete vendors installed, so run
`composer install` before running the tests.

You can run unit tests by calling `composer test` from the repo root:

```bash
$ composer test
```
