# module:make:filament-plugin

Create a new Filament plugin in a module.

## Usage

```bash
php artisan module:make:filament-plugin {name} {module}
```

## Aliases

- `module:filament:plugin`
- `module:filament:make-plugin`

## Arguments

- `name` - The name of the plugin (e.g., `BlogPlugin`, `AnalyticsPlugin`)
- `module` - The name of the module (optional in interactive mode)

## Options

- `--no-interaction`, `-n` - Run in non-interactive mode (requires `module` argument)
- `--force`, `-F` - Overwrite existing files

## Description

Creates a new Filament plugin in your module. Plugins can extend Filament functionality and are automatically registered if `auto-register-plugins` is enabled.

## Examples

### Basic Plugin

```bash
php artisan module:make:filament-plugin BlogPlugin Blog
```

### Plugin with Module Name

```bash
php artisan module:make:filament-plugin Blog Blog
# Creates BlogPlugin.php
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Plugin name
- Module name

## Non-Interactive Mode

When run with `--no-interaction` or in a CI/CD environment (`CI=true`), the command requires all arguments:

```bash
php artisan module:make:filament-plugin BlogPlugin Blog --no-interaction
```

If the `module` argument is missing in non-interactive mode, the command will exit with an error.

## Generated File

The command creates a plugin class at:

```
Modules/{Module}/app/Filament/{Plugin}Plugin.php
```

## Auto-Registration

Plugins are automatically registered if:

- `auto-register-plugins` is `true` in configuration
- The plugin is in the correct namespace
- The `ModulesPlugin` is registered in your panel

## See Also

- [Configuration](../configuration.md)
- [Panels vs Plugins](../guides/panels-vs-plugins.md)

