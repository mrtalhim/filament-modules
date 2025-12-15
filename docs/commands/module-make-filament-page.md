# module:make:filament-page

Create a new Filament page in a module.

## Usage

```bash
php artisan module:make:filament-page {name} {module}
```

## Aliases

- `module:filament:page`
- `module:filament:make-page`

## Arguments

- `name` - The name of the page (e.g., `Dashboard`, `Settings`)
- `module` - The name of the module

## Options

- `--type` - Page type: `custom`, `settings`, `dashboard`
- `--panel` - Specific panel ID (if module has multiple panels)

## Description

Creates a new Filament page in your module. The page will be placed in the appropriate directory based on your module's structure.

## Examples

### Basic Page

```bash
php artisan module:make:filament-page Dashboard Blog
```

### Settings Page

```bash
php artisan module:make:filament-page Settings Blog --type=settings
```

### Custom Page Type

```bash
php artisan module:make:filament-page Reports Blog --type=custom
```

### Page for Specific Panel

```bash
php artisan module:make:filament-page Dashboard Blog --panel=admin
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Page name
- Module name
- Page type
- Panel selection (if multiple panels exist)

## Generated File

The command creates a page class at:

```
Modules/{Module}/app/Filament/{...}/Pages/{Page}.php
```

## See Also

- [Creating Modules](../guides/creating-modules.md)

