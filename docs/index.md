# Filament Modules Documentation

Welcome to the Filament Modules documentation! This package brings the power of modules to Laravel Filament, allowing you to organize your Filament code into fully autonomous modules that can be easily shared and reused across multiple projects.

## Quick Links

- [Installation](installation.md) - Get started with the package
- [Configuration](configuration.md) - Configure the package to your needs
- [Getting Started Guide](guides/getting-started.md) - Quick start tutorial
- [Commands Reference](commands/index.md) - All available commands

## Documentation Structure

### Getting Started
- [Installation](installation.md) - Installation and setup
- [Configuration](configuration.md) - Configuration options
- [Getting Started Guide](guides/getting-started.md) - Step-by-step tutorial

### Commands
- [Commands Overview](commands/index.md) - All available commands
- [Module Filament Install](commands/module-filament-install.md) - Set up Filament in a module
- [Create Panel](commands/module-make-filament-panel.md) - Create a Filament panel
- [Create Resource](commands/module-make-filament-resource.md) - Create a Filament resource
- [Create Page](commands/module-make-filament-page.md) - Create a Filament page
- [Create Widget](commands/module-make-filament-widget.md) - Create a Filament widget
- [Create Cluster](commands/module-make-filament-cluster.md) - Create a Filament cluster
- [Create Plugin](commands/module-make-filament-plugin.md) - Create a Filament plugin
- [Namespace Update](commands/module-namespace-update.md) - Update module namespaces
- [Assets Discovery](commands/module-assets-discover.md) - Discover and register assets
- [Module Validation](commands/module-validate.md) - Validate external projects
- [Module Migration](commands/module-migrate.md) - Migrate external projects
- [Module Health](commands/module-health.md) - Check module health
- [Route Helper](commands/module-route-helper.md) - Generate route helpers

### Guides
- [Creating Modules](guides/creating-modules.md) - Module creation workflow
- [Panels vs Plugins](guides/panels-vs-plugins.md) - When to use panels vs plugins
- [Migration Guide](guides/migration-guide.md) - Migrating external projects
- [Troubleshooting](guides/troubleshooting.md) - Common issues and solutions

### Advanced Topics
- [Customization](advanced/customization.md) - Advanced customization options
- [Extending the Package](advanced/extending.md) - Extend package functionality
- [Architecture](advanced/architecture.md) - Package architecture overview

### Contributing
- [Development Setup](contributing/development.md) - Development environment setup
- [Fork Notes](contributing/fork-notes.md) - Fork-specific development notes

## Package Overview

Filament Modules allows you to:

- **Organize Code**: Structure your Filament code into autonomous modules
- **Share & Reuse**: Easily share modules across multiple projects
- **Independent Panels**: Each module can have its own Filament panel
- **Centralized Auth**: Module panels inherit authentication from main panel
- **Auto-Discovery**: Automatic registration of plugins and panels

## Requirements

| Package Version | Laravel Version | Filament Version | nwidart/laravel-modules Version |
|-----------------|-----------------|------------------|---------------------------------|
| 5.x             | 11.x and 12.x   | 4.x              | 11.x or 12.x                    |
| 4.x             | 11.x and 12.x   | 3.x              | 11.x or 12.x                    |
| 3.x             | 10.x            | 3.x              | 11.x                            |

## Features

- 🛡️ **Centralized Authentication**: Module panels automatically inherit main panel authentication
- 🔄 **Configurable Navigation**: Customizable "Back to Admin" buttons in module panels
- 🏗️ **Independent Panels**: Each module can have its own Filament panel with dedicated URLs
- 📦 **Auto-Discovery**: Automatic registration of plugins and panels
- 🎨 **Asset Management**: Built-in asset discovery and Vite integration
- 🔧 **Developer Tools**: Health checks, validation, and migration tools

## Need Help?

- Check the [Troubleshooting Guide](guides/troubleshooting.md) for common issues
- Review the [Configuration](configuration.md) for customization options
- See [Examples](guides/getting-started.md) for usage patterns

