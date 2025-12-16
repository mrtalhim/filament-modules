# module:make:filament-cluster

Create a new Filament cluster in a module.

## Usage

```bash
php artisan module:make:filament-cluster {name} {module}
```

## Aliases

- `module:filament:cluster`
- `module:filament:make-cluster`

## Arguments

- `name` - The name of the cluster (e.g., `Settings`, `Reports`)
- `module` - The name of the module

## Options

- `--panel` - Panel ID to attach the cluster to
- `--namespace` - The namespace for the cluster (when multiple namespaces exist)
- `--no-interaction`, `-n` - Run in non-interactive mode (requires `name` and `module` arguments)
- `--force`, `-F` - Overwrite existing files

## Description

Creates a new Filament cluster in your module. Clusters allow you to group related resources, pages, and widgets together.

## Examples

### Basic Cluster

```bash
php artisan module:make:filament-cluster Settings Blog
```

### Cluster for Specific Panel

```bash
php artisan module:make:filament-cluster Settings Blog --panel=admin
```

### Non-Interactive Mode

```bash
# Create cluster with all options specified
php artisan module:make:filament-cluster Settings Blog --panel=admin --namespace="Modules\Blog\Filament\Clusters" --no-interaction
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Cluster name
- Module name
- Panel selection
- Namespace selection (if multiple namespaces exist)

## Non-Interactive Mode

When run with `--no-interaction` or in a CI/CD environment (`CI=true`), the command will:

- Require `name` and `module` arguments
- Use default panel if `--panel` is not provided
- Use first available namespace if `--namespace` is not provided
- Skip all prompts and use provided options or sensible defaults

## Generated File

The command creates a cluster class at:

```
Modules/{Module}/app/Filament/Clusters/{Cluster}/{Cluster}Cluster.php
```

## See Also

- [Creating Modules](../guides/creating-modules.md)
- [Panels vs Plugins](../guides/panels-vs-plugins.md)

