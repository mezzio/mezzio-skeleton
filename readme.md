# Expressive Optional Packages Installer

This is a proof of concept for installing optional composer packages based on user input. The user selected packages are saved into ``composer.json`` so everyone else working on the project have the same packages installed. Configuration files and the index.php file are prepared for first use. To not annoy users only missing answers are asked for.

## Testing

1. Run ``composer create-project xtreamwayz/expressive-composer-installer <project-path> --stability="dev"``.
2. Answer question.
3. The chosen packages and dependencies should install and the other one should uninstall, if there are any.
4. CD to ``<project-path>`` and run ``php -S localhost:8000 -t public/``.
5. Open you browser at ``http://localhost:8000`` and there should be a default home page.

## Development

If you want to submit a pull request, make sure you **commit the original composer.json** and not the one updated by the installer!!!
