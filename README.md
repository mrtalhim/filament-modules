# Filament Modules v5.x

[![Latest Version on Packagist](https://img.shields.io/packagist/v/coolsam/modules.svg?style=flat-square)](https://packagist.org/packages/coolsam/modules)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/savannabits/filament-modules/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/savannabits/filament-modules/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/savannabits/filament-modules/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/savannabits/filament-modules/actions?query=workflow%3Afix-php-code-style+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/coolsam/modules.svg?style=flat-square)](https://packagist.org/packages/coolsam/modules)

> **NOTE:** This documentation is for **version 5.x** of the package, which supports **Laravel 11+**, **Filament 4.x** and **nwidart/laravel-modules 11+**. If you are using Filament 3.x, please refer to [4.x documentation](https://github.com/savannabits/filament-modules/tree/4.x) or [3.x documentation](https://github.com/savannabits/filament-modules/tree/3.x) if you are using Laravel 10.

![image](https://github.com/savannabits/filament-modules/assets/5610289/ba191f1d-b5ee-4eb9-9db7-d42a19cc8d38)

This package brings the power of modules to Laravel Filament. It allows you to organize your Filament code into fully autonomous modules that can be easily shared and reused across multiple projects.

With this package, you can turn each of your modules into a fully functional **Filament Plugin** or **independent Filament Panel** with its own resources, pages, widgets, components and more. Module panels feature **centralized authentication** and **configurable navigation** back to your main admin panel.

## 📚 Documentation

**Full documentation is available in the [docs folder](docs/).**

- **[Getting Started](docs/guides/getting-started.md)** - Quick start guide
- **[Installation](docs/installation.md)** - Installation and setup
- **[Configuration](docs/configuration.md)** - Configuration options
- **[Commands Reference](docs/commands/index.md)** - All available commands
- **[Guides](docs/guides/)** - Comprehensive guides and tutorials

## Features

- 🛡️ **Centralized Authentication**: Module panels automatically inherit main panel authentication
- 🔄 **Configurable Navigation**: Customizable "Back to Admin" buttons in module panels
- 🏗️ **Independent Panels**: Each module can have its own Filament panel with dedicated URLs
- 📦 **Auto-Discovery**: Automatic registration of plugins and panels
- 🎨 **Asset Management**: Built-in asset discovery and Vite integration
- 🔧 **Developer Tools**: Health checks, validation, and migration tools
- 🚀 **Quick Setup**: Simple commands to scaffold Filament components in modules

## Requirements

| Package Version | Laravel Version | Filament Version | nwidart/laravel-modules Version |
|-----------------|-----------------|------------------|---------------------------------|
| 5.x             | 11.x and 12.x   | 4.x              | 11.x or 12.x                    |
| 4.x             | 11.x and 12.x   | 3.x              | 11.x or 12.x                    |
| 3.x             | 10.x            | 3.x              | 11.x                            |

v5.x requires:
- Laravel 11.x or 12.x
- Filament 4.x or higher
- PHP 8.2 or higher
- nwidart/laravel-modules 11.x or 12.x

## Quick Installation

```bash
composer require coolsam/modules
```

Register the plugin in your Filament panel:

```php
use Coolsam\Modules\ModulesPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(ModulesPlugin::make());
}
```

Create your first module:

```bash
php artisan module:make Blog
php artisan module:filament:install Blog
php artisan module:make:filament-resource Post Blog --model
```

See the [Installation Guide](docs/installation.md) for detailed setup instructions.

## Quick Start

```bash
# Create a module
php artisan module:make Blog

# Install Filament support
php artisan module:filament:install Blog

# Create a resource with model
php artisan module:make:filament-resource Post Blog --model

# Create a page
php artisan module:make:filament-page Dashboard Blog
```

For more examples, see the [Getting Started Guide](docs/guides/getting-started.md).

## Available Commands

- `module:filament:install` - Set up Filament in a module
- `module:make:filament-panel` - Create a Filament panel
- `module:make:filament-resource` - Create a Filament resource
- `module:make:filament-page` - Create a Filament page
- `module:make:filament-widget` - Create a Filament widget
- `module:make:filament-cluster` - Create a Filament cluster
- `module:make:filament-plugin` - Create a Filament plugin
- `module:namespace:update` - Update module namespaces
- `module:assets:discover` - Discover and register assets
- `module:validate` - Validate external projects
- `module:migrate` - Migrate external projects
- `module:health` - Check module health
- `module:route:helper` - Generate route helpers

See the [Commands Reference](docs/commands/index.md) for complete documentation.

## Documentation

- **[Documentation Index](docs/index.md)** - Start here for all documentation
- **[Installation](docs/installation.md)** - Installation guide
- **[Configuration](docs/configuration.md)** - Configuration reference
- **[Getting Started](docs/guides/getting-started.md)** - Quick start tutorial
- **[Creating Modules](docs/guides/creating-modules.md)** - Module creation guide
- **[Panels vs Plugins](docs/guides/panels-vs-plugins.md)** - Decision guide
- **[Migration Guide](docs/guides/migration-guide.md)** - Migrating projects
- **[Troubleshooting](docs/guides/troubleshooting.md)** - Common issues
- **[Commands Reference](docs/commands/index.md)** - All commands
- **[Advanced Topics](docs/advanced/)** - Customization and extending

## What's New in 5.x

- 🛡️ **Centralized Authentication**: Module panels now automatically inherit authentication from your main Filament panel
- 🔄 **Configurable Back Navigation**: Customizable "Back to Admin" buttons in module panels
- 🏗️ **Independent Module Panels**: Each module can have its own dedicated Filament panel
- ⚙️ **Enhanced Security**: Automatic middleware injection for authentication enforcement
- 🎨 **Asset Discovery**: Automatic asset discovery and Vite integration
- 🔧 **Developer Tools**: Health checks, validation, and migration tools

## Contributing

Contributions are welcome! Please see [Contributing Guide](docs/contributing/development.md) for details.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- 📖 [Full Documentation](docs/)
- 🐛 [Issue Tracker](https://github.com/savannabits/filament-modules/issues)
- 💬 [Discussions](https://github.com/savannabits/filament-modules/discussions)

---

**This package is a wrapper of [nwidart/laravel-modules](https://docs.laravelmodules.com) to make it work with Laravel Filament.**
