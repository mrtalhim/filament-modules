# module:make:filament-panel

Create a new Filament panel in a module.

## Usage

```bash
php artisan module:make:filament-panel {id} {module}
```

## Aliases

- `module:filament:panel`
- `module:filament:make-panel`

## Arguments

- `id` - The ID of the panel (e.g., `admin`, `member`)
- `module` - The name of the module

## Options

- `--label` - Navigation label for the panel
- `--no-auto-register-panel` - Don't auto-register the panel provider

## Description

Creates a new Filament panel provider in your module. The panel will:

- Have a unique ID combining module name and panel ID (e.g., `blog-admin`)
- Be automatically registered (unless `--no-auto-register-panel` is used)
- Include default middleware and authentication
- Have a "Back to Admin" navigation item
- Support resource, page, and widget discovery

## Examples

### Create Admin Panel

```bash
php artisan module:make:filament-panel admin Blog
```

### Create Member Panel with Custom Label

```bash
php artisan module:make:filament-panel member Blog --label="Member Area"
```

### Create Panel Without Auto-Registration

```bash
php artisan module:make:filament-panel admin Blog --no-auto-register-panel
```

## Generated File

The command creates a panel provider at:

```
Modules/{Module}/app/Providers/Filament/{PanelId}PanelProvider.php
```

## Panel Configuration

The generated panel includes:

- Panel ID following the pattern: `{module-slug}-{panel-id}`
- URL path based on `path_strategy` configuration
- Route prefix based on `route_prefix_pattern` configuration
- Default middleware stack
- Authentication middleware
- Resource, page, and widget discovery

## See Also

- [Configuration](../configuration.md#module-panel-configuration)
- [Panels vs Plugins](../guides/panels-vs-plugins.md)

