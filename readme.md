# Zend Expressive Skeleton

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-skeleton.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-skeleton)

*Begin developing PSR-7 middleware applications in seconds!*

[zend-expressive](https://github.com/zendframework/zend-expressive) builds on
[zend-stratigility](https://github.com/zendframework/zend-stratigility) to provide a minimalist PSR-7 middleware
framework for PHP with routing, DI container and optionally templating.

This installer will setup zend-expressive by choosing optional packages based on user input like the following screenshot:

![screenshot-installer](https://cloud.githubusercontent.com/assets/459648/10410494/16bdc674-6f6d-11e5-8190-3c1466e93361.png)

The user selected packages are saved into ``composer.json`` so everyone else working on the project have the same packages installed. Configuration
files and templates are prepared for first use. The installer is removed from ``composer.json`` after setup succeeded.

## Getting Started

Start your new expressive project with composer:

    $ composer create-project zendframework/zend-expressive-skeleton <project-path>

After choosing and installing the packages you want, go to the ``<project-path>`` and start PHP's built-in web server:

    $ php -S localhost:8000 -t public/
