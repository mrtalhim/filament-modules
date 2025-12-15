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

## Interactive Mode

When run without arguments, the command will prompt you:

- Cluster name
- Module name
- Panel selection

## Generated File

The command creates a cluster class at:

```
Modules/{Module}/app/Filament/Clusters/{Cluster}/{Cluster}Cluster.php
```

## See Also

- [Creating Modules](../guides/creating-modules.md)
- [Panels vs Plugins](../guides/panels-vs-plugins.md)

