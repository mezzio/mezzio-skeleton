# Expressive Optional Packages Installer

This is a proof of concept for installing optional composer packages based on user input. The user selected packages are saved into ``composer.json`` so everyone else working on the project have the same packages installed. Configuration files and the index.php file are prepared for first use. To not annoy users only missing answers are asked for. 

## Testing

1. Download this package.
2. Run ``composer update`` to install the packages.
3. Answer question.
4. The chosen packages and dependencies should install and the other one should uninstall, if there are any.

## Development

If you want to submit a pull request, make sure you **commit the original composer.json** and not the one updated by the installer!!!

## TODO:

- [ ] Add option to add packages to ``require-dev``.
- [x] Copy files from Resources dir to other destinations, based on selected packages. This could be useful to copy basic configuration files for each package.
- [x] Store user selections in composer.json. We need to store this in case a custom package was given.
- [x] Only ask question on first run.
- [x] Move none-of-the-above option to the config so packages can be forced. This is useful when at least one needs to be installed for the application to function. (Custom packages can be installed by typing in the package name and version in require format.)
- [x] Test how this works when running ``composer create-project``.
- [x] Use while (true) loop until a valid option is given.
- [x] Remember user selected options.
- [x] Add option to install none of the suggested options.
- [x] Add option to install an other package in stead of the proposed options. Basically you type the composer require package syntax and it searches and adds valid packages.
- [x] Look into / try ``composer update --lock`` to fix the lock file is not up to date issue.
- [x] Use while (true) loop until a valid option is given.
