# module:make:filament-theme

Create a new Filament theme in a module.

## Usage

```bash
php artisan module:make:filament-theme {module}
```

## Aliases

- `module:filament:theme`
- `module:filament:make-theme`

## Arguments

- `module` - The name of the module

## Options

- `--pm` - Panel mode (optional)

## Description

Creates a new Filament theme in your module. Themes allow you to customize the appearance of Filament panels.

## Examples

### Basic Theme

```bash
php artisan module:make:filament-theme Blog
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Module name
- Panel mode selection

## Generated File

The command creates a theme class. Themes can be applied to panels to customize their appearance.

## See Also

- [Creating Modules](../guides/creating-modules.md)
- [Customization](../advanced/customization.md)

