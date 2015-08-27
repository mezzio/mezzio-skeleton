# Expressive Composer Optional Packages Installer

This is a proof of concept for installing optional composer packages based on user input. It kicks in under the following conditions:

- If ``composer install`` is run and there is no ``composer.lock`` file.
- If ``composer update`` is run it should ask which packages to install.

To try it out:

1. Download this package.
2. Run ``composer update`` to install the packages.
3. Answer question.
4. The chosen packages and dependencies should install and the other one should uninstall, if there are any.

## TODO:
- Test how this works out with packages in require-dev.
- Test if a package is manually added in composer.json.
- Copy config files to ``/config/autoload/``.
- Add option to add packages to ``require-dev``.
- For some reason this works as expected for ``composer update``, but not ``composer install``. Probably because install uses the composer.lock file and update ignores it.
- Add a ``--configure`` parameter to run the installer. The installer should only run its configuration if this parameter is detected or on the first run, otherwise it should use previous user selections.
- User config is saved in the same dir as the installer script. Not sure if this is good practice.
- Should the ``filp/whoops`` error handler an option? I think it's for development only right?  
