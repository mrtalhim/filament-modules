# module:make:filament-resource

Create a new Filament resource in a module.

## Usage

```bash
php artisan module:make:filament-resource {name} {module}
```

## Aliases

- `module:filament:resource`
- `module:filament:make-resource`

## Arguments

- `name` - The name of the resource (e.g., `Post`, `Product`)
- `module` - The name of the module

## Options

- `--model` - Create a model for this resource
- `--model-fqn` - Use a fully qualified model class name
- `--generate` - Generate the resource after creation
- `--view` - Generate a view page
- `--simple` - Create a simple resource (no form)

## Description

Creates a new Filament resource in your module. The resource will be placed in the appropriate directory based on your module's structure (clusters or standard Filament directories).

## Examples

### Basic Resource

```bash
php artisan module:make:filament-resource Post Blog
```

### Resource with Model

```bash
php artisan module:make:filament-resource Post Blog --model
```

This creates both the resource and a model in `Modules/Blog/app/Models/Post.php`.

### Resource with FQN Model

```bash
php artisan module:make:filament-resource Order Blog --model-fqn="App\Models\Order"
```

### Resource with View Page

```bash
php artisan module:make:filament-resource Post Blog --model --view
```

### Simple Resource

```bash
php artisan module:make:filament-resource Post Blog --simple
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Resource name
- Module name
- Model selection
- Additional options

## Generated Files

The command creates:

- Resource class: `Modules/{Module}/app/Filament/{...}/Resources/{Resource}Resource.php`
- Resource pages (List, Create, Edit, View)
- Model (if `--model` is used)

## Model Namespace

By default, models are created in `Modules/{Module}/app/Models/`. You can override this by:

- Using `--model-fqn` with a fully qualified namespace
- Configuring the model namespace in your module

## See Also

- [Creating Modules](../guides/creating-modules.md)
- [Getting Started](../guides/getting-started.md)

