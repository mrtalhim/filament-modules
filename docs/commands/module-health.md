# module:health

Check the health of a module and identify potential issues.

## Usage

```bash
php artisan module:health {module} [--fix]
```

## Arguments

- `module` - The name of the module to check

## Options

- `--fix` - Attempt to automatically fix issues

## Description

Comprehensive health check for a module that validates:

- Namespace consistency
- Route validity
- View registration
- Asset configuration
- Service provider registration
- Panel configuration
- File structure

## Examples

### Basic Health Check

```bash
php artisan module:health Blog
```

### Health Check with Auto-Fix

```bash
php artisan module:health Blog --fix
```

## Health Checks Performed

### 1. Namespace Consistency

- Verifies namespace declarations match file locations
- Checks class names match file names
- Identifies inconsistent namespaces

### 2. Route Validity

- Validates all `route()` calls
- Checks for non-existent routes
- Warns about generic route patterns

### 3. View Registration

- Verifies view namespace is registered
- Checks views directory exists
- Validates service provider configuration

### 4. Asset Configuration

- Identifies CSS/JS files
- Warns if assets need Vite registration
- Suggests asset discovery command

### 5. Service Provider Registration

- Verifies service provider exists
- Checks autoload configuration
- Validates registration

### 6. Panel Configuration

- Lists all panels
- Validates panel IDs
- Checks naming conventions

### 7. File Structure

- Verifies required directories exist
- Checks for `.gitignore` and `README.md`
- Validates module structure

## Output

The command displays:

- ✅ Checks performed count
- ❌ Critical issues (must fix)
- ⚠️ Warnings (should review)
- 🔧 Auto-fix attempts (if `--fix` used)

## Auto-Fix

With `--fix` flag, the command attempts to:

- Register view namespaces
- Fix common configuration issues
- Suggest manual fixes for complex issues

## See Also

- [Getting Started](../guides/getting-started.md)
- [Troubleshooting](../guides/troubleshooting.md)

