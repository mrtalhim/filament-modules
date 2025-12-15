# Installation

This guide will help you install and configure Filament Modules in your Laravel application.

## Requirements

v5.x of this package requires:

- Laravel 11.x or 12.x
- Filament 4.x or higher
- PHP 8.2 or higher
- nwidart/laravel-modules 11.x or 12.x

## Installation Steps

### 1. Install the Package

Install the package via Composer:

```bash
composer require coolsam/modules
```

This will automatically install `nwidart/laravel-modules: ^11` (for Laravel 11) or `nwidart/laravel-modules: ^12` (for Laravel 12) as well.

### 2. Configure Laravel Modules

**Important**: Configure your Laravel Modules first before continuing.

Make sure you go through the [nwidart/laravel-modules documentation](https://laravelmodules.com/docs/12) to understand how to use the package and configure it properly.

### 3. Autoload Modules

Don't forget to autoload modules by adding the merge-plugin to your `composer.json` according to the [laravel modules documentation](https://laravelmodules.com/docs/12/getting-started/installation-and-setup#autoloading):

```json
{
    "extra": {
        "laravel": {
            "dont-discover": []
        },
        "merge-plugin": {
            "include": [
                "Modules/*/composer.json"
            ]
        }
    }
}
```

### 4. Install Laravel Modules

Run the installation command and follow the prompts to publish the config file and set up the package:

```bash
php artisan modules:install
```

Alternatively, you can just publish the config file with:

```bash
php artisan vendor:publish --tag="modules-config"
```

### 5. Publish Filament Modules Config

Publish the Filament Modules configuration file:

```bash
php artisan vendor:publish --tag="filament-modules-config"
```

The configuration file will be published to `config/filament-modules.php`.

### 6. Register the Plugin

Register the `ModulesPlugin` in your Filament Panel:

```php
// e.g. in App\Providers\Filament\AdminPanelProvider.php

use Coolsam\Modules\ModulesPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other panel configuration
        ->plugin(ModulesPlugin::make());
}
```

That's it! You're now ready to start creating Filament code in your modules.

## Next Steps

- Read the [Configuration Guide](configuration.md) to customize the package
- Follow the [Getting Started Guide](guides/getting-started.md) to create your first module
- Check out the [Commands Reference](commands/index.md) for available commands

## Fork Installation

If you're installing from a fork, see the [Fork Installation Guide](contributing/fork-notes.md#fork-installation) for specific instructions.

