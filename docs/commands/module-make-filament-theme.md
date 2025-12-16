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

- `--pm` - Package manager (npm, yarn, pnpm) - defaults to npm
- `--force`, `-F` - Overwrite existing files
- `--no-interaction`, `-n` - Run in non-interactive mode (requires `module` argument)

## Description

Creates a new Filament theme in your module. Themes allow you to customize the appearance of Filament panels.

## Examples

### Basic Theme

```bash
php artisan module:make:filament-theme Blog
```

### Non-Interactive Mode

```bash
# Create theme with module specified
php artisan module:make:filament-theme Blog --no-interaction
```

## Interactive Mode

When run without arguments, the command will prompt you:

- Module name

## Non-Interactive Mode

When run with `--no-interaction` or in a CI/CD environment (`CI=true`), the command will:

- Require `module` argument
- Skip all prompts and use provided options or sensible defaults

## Generated File

The command creates a theme class. Themes can be applied to panels to customize their appearance.

## See Also

- [Creating Modules](../guides/creating-modules.md)
- [Customization](../advanced/customization.md)

