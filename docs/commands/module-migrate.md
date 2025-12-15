# module:migrate

Migrate external Filament project into a module with interactive wizard.

## Usage

```bash
php artisan module:migrate {module} [--source=/path/to/project] [--interactive]
```

## Arguments

- `module` - The name of the target module

## Options

- `--source` - Path to the external project source
- `--interactive` - Run in interactive mode (default if not specified)

## Description

Interactive wizard for migrating an external Filament project into a module. Handles:

- Namespace updates
- Route name migration
- Asset configuration
- Shared model identification
- File copying

## Examples

### Interactive Migration

```bash
php artisan module:migrate Blog
# Follow prompts for source path and options
```

### Non-Interactive Migration

```bash
php artisan module:migrate Blog --source=/path/to/project
```

## Migration Steps

### 1. Validation

Runs `module:validate` to check compatibility.

### 2. Planning

Interactive prompts for:
- Namespace updates (from/to)
- Route migration patterns
- Asset handling (copy/link/vite/ignore)
- Shared model handling

### 3. Execution

- Copies Filament files
- Applies namespace updates
- Migrates routes
- Handles assets
- Processes shared models

### 4. Post-Migration

- Generates route helpers (optional)
- Runs `composer dump-autoload`
- Runs health check

## Interactive Prompts

When run interactively, you'll be asked:

1. **Source Path**: Where is the external project located?
2. **Namespace Updates**: Update namespaces? (from/to)
3. **Route Migration**: Migrate route names? (prefix patterns)
4. **Asset Handling**: How to handle assets? (copy/link/vite/ignore)
5. **Shared Models**: Identify and handle shared models?

## See Also

- [Module Validate Command](module-validate.md)
- [Migration Guide](../guides/migration-guide.md)
- [Module Namespace Update Command](module-namespace-update.md)

