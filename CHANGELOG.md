# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0rc4 - 2015-12-09

Fourth release candidate.

### Added

- [#34](https://github.com/zendframework/zend-expressive-skeleton/pull/34)
  updates the zend-view configuration to register a factory for
  `Zend\View\HelperPluginManager`, as well as a `view_helpers` sub-key for
  registering custom view helpers.
- [#37](https://github.com/zendframework/zend-expressive-skeleton/pull/37)
  creates the subdirectories `src/App/` and `test/AppTest/`, moving the
  subdirectories of each under those, and updating the `composer.json`
  autoloading directives accordingly. This change will allow new projects to
  implement a "modular" structure if desired, with a subdirectory per namespace.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#23](https://github.com/zendframework/zend-expressive-skeleton/pull/23)
  updates the comment for the glob statements to ensure all 4 (not just 2!)
  possible matches are detailed.
- [#24](https://github.com/zendframework/zend-expressive-skeleton/pull/24)
  updates the `config/config.php` file to store cached configuration as a plain
  PHP file, so that it can simply `include()`; this will be faster than using
  JSON-serialized structures.
- [#30](https://github.com/zendframework/zend-expressive-skeleton/pull/30)
  updates the Twig configuration to follow the changes made for
  [zendframework/zend-expressive-twigrenderer 0.3.0](https://github.com/zendframework/zend-expressive-twigrenderer/releases/tag/0.3.0).
  The old configuration format will still work, though users *should* update
  their configuration to the new format. The change in this patch only affects
  new installs.
- [#33](https://github.com/zendframework/zend-expressive-skeleton/pull/33)
  updates to zendframework/zend-expressive-helpers `^1.2`.
- [#33](https://github.com/zendframework/zend-expressive-skeleton/pull/33) adds
  configuration for auto-registering the new `Zend\Expressive\Helper\UrlHelperMiddleware`
  as pipeline middleware; this fixes an issue when using the zend-view renderer
  with the `url()` helper whereby the `UrlHelper` was being registered as a
  route result observer too late to receive the `RouteResult`.
- [#40](https://github.com/zendframework/zend-expressive-skeleton/pull/40)
  renames the namespace for the installer to `ExpressiveInstaller`.

## 1.0.0rc3 - 2015-12-07

Third release candidate.

### Added

- [#20](https://github.com/zendframework/zend-expressive-skeleton/pull/20) adds
  the ability to specify a "minimal" install; when selected, the installer will
  install modified configuration, omit some files, and remove the default
  middleware and public assets.
- [#27](https://github.com/zendframework/zend-expressive-skeleton/pull/27) adds
  [zendframework/zend-expressive-helpers](https://github.com/zendframework/zend-expressive-helpers)
  as a dependency, and integrates the helpers into the configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#13](https://github.com/zendframework/zend-expressive-skeleton/pull/13)
  updates the installer to also remove the dependency on composer/composer
  on completion.
- [#11](https://github.com/zendframework/zend-expressive-skeleton/pull/11)
  moves the route middleware service definitions into the routes configuration
  files.
- [#21](https://github.com/zendframework/zend-expressive-skeleton/pull/21)
  updates `require` statements in generated configuration files to use the
  `__DIR__` constant to ensure files are located relative to the origin file.
- [#25](https://github.com/zendframework/zend-expressive-skeleton/pull/25) and
  [#29](https://github.com/zendframework/zend-expressive-skeleton/pull/29)
  update minimum versions for each router and template implementation (final
  versions for RC3 are all at `^1.0`).
- [#29](https://github.com/zendframework/zend-expressive-skeleton/pull/29) sets
  the zend-expressive required version to `~1.0.0@rc || ^1.0`, to ensure a
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

- Updated expressive to RC2.
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

- [#8](https://github.com/zendframework/zend-expressive-skeleton/pull/8) adds a
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

- [#7](https://github.com/zendframework/zend-expressive-skeleton/pull/7) adds a
  dependency on zend-stdlib for the purposes of globbing and merging
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

- [#6](https://github.com/zendframework/zend-expressive-skeleton/pull/6) updates
  the zendframework/zend-view package configuration to remove the dependency on
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

- [#3](https://github.com/zendframework/zend-expressive-skeleton/pull/3) updates
  the skeleton to use zendframework/zend-expressive 0.4.0.

## 0.4.0 - 2015-10-09

First release as zend-expressive-skeleton.

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

- Use zend-expressive template factories.
- Use the zend view url helper in the layout template.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2015-09-11

### Added

- [#bbb2e60](https://github.com/xtreamwayz/expressive-composer-installer/commit/bbb2e607af23e3ae23f6a9c71eb97c3c651c0ca1) adds PHPUnit tests.
- [#791c1c6](https://github.com/xtreamwayz/expressive-composer-installer/commit/791c1c63f324ca08d08e26375f3a356102bf2ad9) adds Whoops error handler.
- [e1d8d7bf](https://github.com/xtreamwayz/expressive-composer-installer/commit/e1d8d7bf5d5e2f51863fa59a37d1963405743201) adds config caching in production mode.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2015-09-08

### Added

- [#b4a0923](https://github.com/xtreamwayz/expressive-composer-installer/commit/b4a092386993227f8057d7ad4e0d9762659eefb0) adds support for Pimple 3.0.x. Still needs testing!

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#11](https://github.com/xtreamwayz/expressive-composer-installer/issues/11) fixes an issues where non stable packages are not being installed correctly.

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
