# Expressive Composer Installer

[![Build Status](https://secure.travis-ci.org/xtreamwayz/expressive-composer-installer.svg?branch=master)](https://secure.travis-ci.org/xtreamwayz/expressive-composer-installer)

*Begin developing PSR-7 middleware applications in seconds!*

[zend-expressive](https://github.com/zendframework/zend-expressive) builds on
[zend-stratigility](https://github.com/zendframework/zend-stratigility) to provide a minimalist PSR-7 middleware
framework for PHP with routing, DI container and optionally templating.

This installer will setup zend-expressive by choosing optional packages based on user input. The user selected packages
are saved into ``composer.json`` so everyone else working on the project have the same packages installed. Configuration
files and templates are prepared for first use. The installer is removed from ``composer.json`` after setup succeeded.

## Known Issues

- Not all zend-view plugins are hooked up yet.

## Testing

1. Run ``composer create-project xtreamwayz/expressive-composer-installer <project-path> --stability="dev"``.
2. Answer the questions.
3. The chosen packages and dependencies should install.
4. CD to ``<project-path>`` and run ``php -S localhost:8000 -t public/``.
5. Open you browser at ``http://localhost:8000`` and there should be a welcome page waiting for you.
