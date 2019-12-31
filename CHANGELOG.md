# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0rc7 - 2016-01-19

Seventh release candidate.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-skeleton#64](https://github.com/zendframework/zend-expressive-skeleton/pull/64)
  fixes the installer script to correctly rewrite the `require-dev` section
  and ensure only the development dependencies selected, as well as base 
  requirements such as PHPUnit and PHP_CodeSniffer, are installed. As such,
  the `--no-dev` flag is no longer required, and development dependencies
  such as whoops are properly installed.

## 1.0.0rc6 - 2016-01-19

Sixth release candidate.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-skeleton#56](https://github.com/zendframework/zend-expressive-skeleton/pull/56)
  updates the `composer serve` command to include the `public/index.php` script
  as an argument. This ensures that asset paths that the application could
  intercept and serve will be passed to the application (previously, the
  built-in server would treat these as 404s, and never pass them to the
  application).
- [zendframework/zend-expressive-skeleton#57](https://github.com/zendframework/zend-expressive-skeleton/pull/57)
  updates the Apache configuration rules defined in `public/.htaccess` to omit
  several that could prevent the application from intercepting requests for
  assets.
- [zendframework/zend-expressive-skeleton#52](https://github.com/zendframework/zend-expressive-skeleton/pull/52)
  fixes the switch statement in the `HomePageAction` class to ensure the
  template name and documentation link are accurately found.
- [zendframework/zend-expressive-skeleton#59](https://github.com/zendframework/zend-expressive-skeleton/pull/59)
  updates the `config/container.php` implementation for laminas-servicemanager such
  that it can work with either v2 or v3 of that library.
- [zendframework/zend-expressive-skeleton#60](https://github.com/zendframework/zend-expressive-skeleton/pull/60)
  updates the mezzio-helpers dependency to `^2.0`, and updates the
  `config/autoload/middleware-pipeline.global.php` to follow the changes in
  middleware configuration introduced in [mezzio zendframework/zend-expressive-skeleton#270](https://github.com/zendframework/zend-expressive/pull/270).
  The change introduces convention-based keys for "always" (execute before
  routing), "routing" (routing, listeners that act on the route result, and
  dispatching), and "error", with reasonable priorities to ensure execution
  order.
- [zendframework/zend-expressive-skeleton#60](https://github.com/zendframework/zend-expressive-skeleton/pull/60)
  fixes the documentation for `composer create-project` to include the
  `--no-dev` flag; this is done as composer currently installs the development
  dependencies listed before the installer script rewrites the `composer.json`
  file. Running `composer update` or `composer install` within the project
  directory after the initial installation will install the development
  dependencies.

## 1.0.0rc5 - 2015-12-22

Fifth release candidate.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-skeleton#42](https://github.com/zendframework/zend-expressive-skeleton/pull/42)
  fixes some grammatical issues in the questions presented by the installer.
- [zendframework/zend-expressive-skeleton#45](https://github.com/zendframework/zend-expressive-skeleton/pull/45)
  fixes how JS and CSS assets are added to laminas-view templates.
- [zendframework/zend-expressive-skeleton#48](https://github.com/zendframework/zend-expressive-skeleton/pull/48)
  adds unit tests for the `OptionalPackages` class (which provides the Composer
  installer scripts).
- [zendframework/zend-expressive-skeleton#49](https://github.com/zendframework/zend-expressive-skeleton/pull/49)
  updates the Pimple support to Pimple v3, ensuring Pimple users are using the
  latest stable release.

## 1.0.0rc4 - 2015-12-09

Fourth release candidate.

### Added

- [zendframework/zend-expressive-skeleton#34](https://github.com/zendframework/zend-expressive-skeleton/pull/34)
  updates the laminas-view configuration to register a factory for
  `Laminas\View\HelperPluginManager`, as well as a `view_helpers` sub-key for
  registering custom view helpers.
- [zendframework/zend-expressive-skeleton#37](https://github.com/zendframework/zend-expressive-skeleton/pull/37)
  creates the subdirectories `src/App/` and `test/AppTest/`, moving the
  subdirectories of each under those, and updating the `composer.json`
  autoloading directives accordingly. This change will allow new projects to
  implement a "modular" structure if desired, with a subdirectory per namespace.
- [zendframework/zend-expressive-skeleton#41](https://github.com/zendframework/zend-expressive-skeleton/pull/41) adds
  the composer script "serve", which fires up the built-in PHP webserver on port
  8080; invoke using `composer serve`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-skeleton#23](https://github.com/zendframework/zend-expressive-skeleton/pull/23)
  updates the comment for the glob statements to ensure all 4 (not just 2!)
  possible matches are detailed.
- [zendframework/zend-expressive-skeleton#24](https://github.com/zendframework/zend-expressive-skeleton/pull/24)
  updates the `config/config.php` file to store cached configuration as a plain
  PHP file, so that it can simply `include()`; this will be faster than using
  JSON-serialized structures.
- [zendframework/zend-expressive-skeleton#30](https://github.com/zendframework/zend-expressive-skeleton/pull/30)
  updates the Twig configuration to follow the changes made for
  [mezzio/mezzio-twigrenderer 0.3.0](https://github.com/mezzio/mezzio-twigrenderer/releases/tag/0.3.0).
  The old configuration format will still work, though users *should* update
  their configuration to the new format. The change in this patch only affects
  new installs.
- [zendframework/zend-expressive-skeleton#33](https://github.com/zendframework/zend-expressive-skeleton/pull/33)
  updates to zendframework/zend-expressive-helpers `^1.2`.
- [zendframework/zend-expressive-skeleton#33](https://github.com/zendframework/zend-expressive-skeleton/pull/33) adds
  configuration for auto-registering the new `Mezzio\Helper\UrlHelperMiddleware`
  as pipeline middleware; this fixes an issue when using the laminas-view renderer
  with the `url()` helper whereby the `UrlHelper` was being registered as a
  route result observer too late to receive the `RouteResult`.
- [zendframework/zend-expressive-skeleton#40](https://github.com/zendframework/zend-expressive-skeleton/pull/40)
  renames the namespace for the installer to `MezzioInstaller`.

## 1.0.0rc3 - 2015-12-07

Third release candidate.

### Added

- [zendframework/zend-expressive-skeleton#20](https://github.com/zendframework/zend-expressive-skeleton/pull/20) adds
  the ability to specify a "minimal" install; when selected, the installer will
  install modified configuration, omit some files, and remove the default
  middleware and public assets.
- [zendframework/zend-expressive-skeleton#27](https://github.com/zendframework/zend-expressive-skeleton/pull/27) adds
  [mezzio/mezzio-helpers](https://github.com/zendframework/zend-expressive-helpers)
  as a dependency, and integrates the helpers into the configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-skeleton#13](https://github.com/zendframework/zend-expressive-skeleton/pull/13)
  updates the installer to also remove the dependency on composer/composer
  on completion.
- [zendframework/zend-expressive-skeleton#11](https://github.com/zendframework/zend-expressive-skeleton/pull/11)
  moves the route middleware service definitions into the routes configuration
  files.
- [zendframework/zend-expressive-skeleton#21](https://github.com/zendframework/zend-expressive-skeleton/pull/21)
  updates `require` statements in generated configuration files to use the
  `__DIR__` constant to ensure files are located relative to the origin file.
- [zendframework/zend-expressive-skeleton#25](https://github.com/zendframework/zend-expressive-skeleton/pull/25) and
  [zendframework/zend-expressive-skeleton#29](https://github.com/zendframework/zend-expressive-skeleton/pull/29)
  update minimum versions for each router and template implementation (final
  versions for RC3 are all at `^1.0`).
- [zendframework/zend-expressive-skeleton#29](https://github.com/zendframework/zend-expressive-skeleton/pull/29) sets
  the mezzio required version to `~1.0.0@rc || ^1.0`, to ensure a
  stable version is always installed.

## 1.0.0rc2 - 2015-10-20

Second release candidate.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Updated mezzio to RC2.
- Updated subcomponent versions in installer to `^0.2`

## 1.0.0rc1 - 2015-10-19

First release candidate.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.3 - 2015-10-16

### Added

- [zendframework/zend-expressive-skeleton#8](https://github.com/zendframework/zend-expressive-skeleton/pull/8) adds a
  routine to the installer that recursively removes the `src/Composer/`
  directory of the skeleton, ensuring you have a clean start when creating a
  project.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.2 - 2015-10-13

### Added

- [zendframework/zend-expressive-skeleton#7](https://github.com/zendframework/zend-expressive-skeleton/pull/7) adds a
  dependency on laminas-stdlib for the purposes of globbing and merging
  configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.1 - 2015-10-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-skeleton#6](https://github.com/zendframework/zend-expressive-skeleton/pull/6) updates
  the laminas/laminas-view package configuration to remove the dependency on
  zendframework/zend-i18n, as it is now handled in the standalone
  zend-expressive-zendviewrenderer package.

## 0.5.0 - 2015-10-10

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-skeleton#3](https://github.com/zendframework/zend-expressive-skeleton/pull/3) updates
  the skeleton to use mezzio/mezzio 0.4.0.

## 0.4.0 - 2015-10-09

First release as mezzio-skeleton.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2015-09-12

### Added

- Use mezzio template factories.
- Use the laminas view url helper in the layout template.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2015-09-11

### Added

- [#bbb2e60](https://github.com/xtreamwayz/mezzio-composer-installer/commit/bbb2e607af23e3ae23f6a9c71eb97c3c651c0ca1) adds PHPUnit tests.
- [zendframework/zend-expressive-skeleton#791c1c6](https://github.com/xtreamwayz/mezzio-composer-installer/commit/791c1c63f324ca08d08e26375f3a356102bf2ad9) adds Whoops error handler.
- [e1d8d7bf](https://github.com/xtreamwayz/mezzio-composer-installer/commit/e1d8d7bf5d5e2f51863fa59a37d1963405743201) adds config caching in production mode.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2015-09-08

### Added

- [#b4a0923](https://github.com/xtreamwayz/mezzio-composer-installer/commit/b4a092386993227f8057d7ad4e0d9762659eefb0) adds support for Pimple 3.0.x. Still needs testing!

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-skeleton#11](https://github.com/xtreamwayz/mezzio-composer-installer/issues/11) fixes an issues where non stable packages are not being installed correctly.

## 0.1.0 - 2015-09-07

Initial tagged release.

### Added

- Everything.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
