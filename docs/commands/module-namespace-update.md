# module:namespace:update

Update namespaces in a module from one pattern to another.

## Usage

```bash
php artisan module:namespace:update {module} --from="Old\\Namespace\\" --to="New\\Namespace\\"
```

## Arguments

- `module` - The name of the module to update

## Options

- `--from` - The namespace to replace (e.g., `"App\\"`)
- `--to` - The new namespace (e.g., `"Modules\\MyModule\\"`)

## Description

This command recursively finds and replaces namespaces in all PHP files within a module. It's useful when:

- Migrating code from an external project
- Refactoring module namespaces
- Consolidating namespaces

## Examples

### Update from App to Module Namespace

```bash
php artisan module:namespace:update Blog --from="App\\" --to="Modules\\Blog\\"
```

### Update Specific Namespace

```bash
php artisan module:namespace:update Blog --from="App\\Models\\" --to="Modules\\Blog\\Models\\"
```

## What Gets Updated

The command updates:

- Namespace declarations (`namespace App\...`)
- Use statements (`use App\...`)
- Fully qualified class names (`new App\...`)
- Type hints and static calls

## Safety

After running this command:

1. Run `composer dump-autoload` to refresh the autoloader
2. Test your application thoroughly
3. Review the changes in version control

## Output

The command displays:

- Number of files updated
- Total replacements made
- Warnings about autoloader refresh

## See Also

- [Migration Guide](../guides/migration-guide.md)
- [Module Migrate Command](module-migrate.md)

