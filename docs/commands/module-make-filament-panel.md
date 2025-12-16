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

- `id` - The ID of the panel (e.g., `admin`, `member`) (optional in interactive mode)
- `module` - The name of the module (optional in interactive mode)

## Options

- `--label` - Navigation label for the panel (auto-generated from panel ID if not provided in non-interactive mode)
- `--no-auto-register-panel` - Don't auto-register the panel provider
- `--force`, `-F` - Overwrite existing files
- `--no-interaction`, `-n` - Run in non-interactive mode (requires `id` and `module` arguments)

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

### Non-Interactive Mode

```bash
# Create panel with all options specified
php artisan module:make:filament-panel admin Blog --label="Admin Panel" --no-interaction

# Panel ID and label will be auto-generated if not provided
php artisan module:make:filament-panel admin Blog --no-interaction
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Panel ID
- Module name
- Navigation label

## Non-Interactive Mode

When run with `--no-interaction` or in a CI/CD environment (`CI=true`), the command will:

- Require `id` and `module` arguments
- Auto-generate label from panel ID if `--label` is not provided
- Use default panel ID (`default`) if `id` is not provided
- Skip all prompts and use provided options or sensible defaults

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

