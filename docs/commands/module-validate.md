# module:validate

Validate external project compatibility before module integration.

## Usage

```bash
php artisan module:validate {path} [--target-module=ModuleName]
```

## Arguments

- `path` - Path to the external project to validate

## Options

- `--target-module` - Target module name for integration

## Description

Validates an external Filament project for compatibility before integrating it as a module. Checks for:

- Filament version compatibility
- Namespace conflicts
- Route name patterns
- Required dependencies
- Shared models

## Examples

### Validate External Project

```bash
php artisan module:validate /path/to/external-project
```

### Validate with Target Module

```bash
php artisan module:validate /path/to/external-project --target-module=Blog
```

## Validation Checks

### 1. Filament Version Compatibility

Checks if the external project's Filament version matches your current project.

### 2. Namespace Conflicts

Identifies namespaces that might conflict with the Modules namespace.

### 3. Route Name Patterns

Warns about generic route names that may conflict with module routes.

### 4. Required Dependencies

Verifies that required packages (Filament, Laravel) are present.

### 5. Shared Models

Detects models that should potentially use main project models instead.

## Output

The command displays:

- ✅ Passed checks
- ❌ Critical issues (must fix)
- ⚠️ Warnings (should review)

## See Also

- [Module Migrate Command](module-migrate.md)
- [Migration Guide](../guides/migration-guide.md)

