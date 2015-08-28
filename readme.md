# Expressive Optional Packages Installer

This is a proof of concept for installing optional composer packages based on user input. It kicks in under the following conditions:

- If ``composer install`` is run and there is no ``composer.lock`` file.
- If ``composer update`` is run it should ask which packages to install.

To try it out:

1. Download this package.
2. Run ``composer update`` to install the packages.
3. Answer question.
4. The chosen packages and dependencies should install and the other one should uninstall, if there are any.

## TODO:

- [ ] Test how this works when running ``composer create-project``.
- [ ] Copy config files from Resources to other destinations, based on selected packages.
- [ ] Add option to add packages to ``require-dev``.
- [ ] Use while (true) loop until a valid option is given.
- [x] Remember user selected options.
- [x] Add option to install none of the suggested options.
- [x] Add option to install an other package in stead of the proposed options. Basically you type the composer require package syntax and it searches and adds valid packages.
- [x] Look into / try ``composer update --lock`` to fix the lock file is not up to date issue.
- [x] Use while (true) loop until a valid option is given.
