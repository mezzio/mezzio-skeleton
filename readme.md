# Zend Framework Expressive Composer Installer

This is a proof of concept for a composer installer based on user input.

1. Download this package.
2. Run composer update to install the router.
3. Answer question.
4. The chosen router package should install and the other one should uninstall.

## TODO:
- Test how this works out with packages in require-dev.
- For some reason this works as expected for ``composer update``, but not ``composer install``. Probably because install uses the composer.lock file and update ignores it.
- Figure out a way to save the user selection for ``composer update``.
- Add a ``--configure`` parameter to run the installer. The installer should only run its configuration if this parameter is detected or on the first run, otherwise it should use previous user selections.
- Should the ``filp/whoops`` error handler an option? I think it's for development only right?  
