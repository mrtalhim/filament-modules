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

## Interactive Mode

When run without arguments, the command will prompt you:

- Widget name
- Module name
- Widget type

## Generated File

The command creates a widget class at:

```
Modules/{Module}/app/Filament/{...}/Widgets/{Widget}.php
```

## See Also

- [Creating Modules](../guides/creating-modules.md)

