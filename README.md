# Expressive Skeleton and Installer

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-skeleton.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-skeleton)

*Begin developing PSR-7 middleware applications in seconds!*

[zend-expressive](https://github.com/zendframework/zend-expressive) builds on
[zend-stratigility](https://github.com/zendframework/zend-stratigility) to
provide a minimalist PSR-7 middleware framework for PHP with routing, DI
container, optional templating, and optional error handling capabilities.

This installer will setup a skeleton application based on zend-expressive by
choosing optional packages based on user input as demonstrated in the following
screenshot:

![screenshot-installer](https://cloud.githubusercontent.com/assets/459648/10410494/16bdc674-6f6d-11e5-8190-3c1466e93361.png)

The user selected packages are saved into `composer.json` so that everyone else
working on the project have the same packages installed. Configuration files and
templates are prepared for first use. The installer command is removed from
`composer.json` after setup succeeded, and all installer related files are
removed.

## Getting Started

Start your new Expressive project with composer:

```bash
$ composer create-project zendframework/zend-expressive-skeleton <project-path>
```

After choosing and installing the packages you want, go to the
`<project-path>` and start PHP's built-in web server to verify installation:

```bash
$ composer serve
```

You can then browse to http://localhost:8080.

> ### Setting a timeout
>
> Composer commands time out after 300 seconds (5 minutes). On Linux-based
> systems, the `php -S` command that `composer server` spawns continues running
> as a background process, but on other systems halts when the timeout occurs.
>
> If you want the server to live longer, you can use the
> `COMPOSER_PROCESS_TIMEOUT` environment variable when executing `composer
> serve` to extend the timeout. As an example, the following will extend it
> to a full day:
>
> ```bash
> $ COMPOSER_PROCESS_TIMEOUT=86400 composer serve
> ```

## Skeleton Development

This section applies only if you cloned this repo with `git clone`, not when you installed expressive with
`composer create-project ...`.

If you want to run tests against the installer, you need to clone this repo and setup all dependencies with composer.
Make sure you **prevent composer running scripts** with `--no-scripts`, otherwise it will remove the installer and
all tests.

```bash
$ composer install --no-scripts
$ composer test
```

Please note that the installer tests remove installed config files and templates before and after running the tests.

Before contributing read [the contributing guide](CONTRIBUTING.md).
