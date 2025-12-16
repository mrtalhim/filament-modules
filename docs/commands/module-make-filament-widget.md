# module:make:filament-widget

Create a new Filament widget in a module.

## Usage

```bash
php artisan module:make:filament-widget {name} {module}
```

## Aliases

- `module:filament:widget`
- `module:filament:make-widget`

## Arguments

- `name` - The name of the widget (e.g., `StatsOverview`, `RecentPosts`)
- `module` - The name of the module

## Options

- `--chart` - Create a chart widget
- `--table` - Create a table widget
- `--stats-overview` - Create a stats overview widget
- `--panel` - Specific panel ID (if module has multiple panels)
- `--namespace` - The namespace for the widget (when multiple namespaces exist)
- `--view-namespace` - The view namespace for the widget
- `--no-interaction`, `-n` - Run in non-interactive mode (requires `name` and `module` arguments)
- `--force`, `-F` - Overwrite existing files

## Description

Creates a new Filament widget in your module. Widgets can be displayed on dashboard pages or embedded in resources.

## Examples

### Basic Widget

```bash
php artisan module:make:filament-widget RecentPosts Blog
```

### Chart Widget

```bash
php artisan module:make:filament-widget PostChart Blog --chart
```

### Table Widget

```bash
php artisan module:make:filament-widget RecentPostsTable Blog --table
```

### Stats Overview Widget

```bash
php artisan module:make:filament-widget BlogStats Blog --stats-overview
```

### Non-Interactive Mode

```bash
# Create widget with all options specified
php artisan module:make:filament-widget RecentPosts Blog --panel=admin --namespace="Modules\Blog\Filament\Widgets" --no-interaction
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Widget name
- Module name
- Widget type
- Panel selection (if multiple panels exist)
- Namespace selection (if multiple namespaces exist)
- View location

## Non-Interactive Mode

When run with `--no-interaction` or in a CI/CD environment (`CI=true`), the command will:

- Require `name` and `module` arguments
- Use default panel if `--panel` is not provided
- Use first available namespace if `--namespace` is not provided
- Skip all prompts and use provided options or sensible defaults

## Generated File

The command creates a widget class at:

```
Modules/{Module}/app/Filament/{...}/Widgets/{Widget}.php
```

## See Also

- [Creating Modules](../guides/creating-modules.md)

