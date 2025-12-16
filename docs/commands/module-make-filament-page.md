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
- `--cluster` - The cluster FQN for the page
- `--namespace` - The namespace for the page (when multiple namespaces exist)
- `--view-namespace` - The view namespace for the page
- `--no-interaction`, `-n` - Run in non-interactive mode (requires `name` and `module` arguments)
- `--force`, `-F` - Overwrite existing files

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

### Non-Interactive Mode

```bash
# Create page with all options specified
php artisan module:make:filament-page Dashboard Blog --panel=admin --namespace="Modules\Blog\Filament\Pages" --no-interaction

# With cluster
php artisan module:make:filament-page Settings Blog --cluster="Modules\Blog\Filament\Clusters\Settings" --no-interaction
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Page name
- Module name
- Page type
- Panel selection (if multiple panels exist)
- Cluster selection (if clusters exist)
- Namespace selection (if multiple namespaces exist)
- View location

## Non-Interactive Mode

When run with `--no-interaction` or in a CI/CD environment (`CI=true`), the command will:

- Require `name` and `module` arguments
- Use default panel if `--panel` is not provided
- Skip cluster selection if `--cluster` is not provided
- Use first available namespace if `--namespace` is not provided
- Skip all prompts and use provided options or sensible defaults

## Generated File

The command creates a page class at:

```
Modules/{Module}/app/Filament/{...}/Pages/{Page}.php
```

## See Also

- [Creating Modules](../guides/creating-modules.md)

