# Migration Guide

Guide for migrating external Filament projects into modules.

## Overview

The migration process helps you convert an existing Filament project into a module structure. This is useful when:

- Consolidating multiple projects
- Modularizing existing code
- Sharing code between projects

## Prerequisites

- External Filament project
- Target module created
- Filament Modules installed

## Migration Process

### Step 1: Validate Project

First, validate the external project for compatibility:

```bash
php artisan module:validate /path/to/external-project --target-module=Blog
```

This checks:
- Filament version compatibility
- Namespace conflicts
- Route name patterns
- Required dependencies

### Step 2: Run Migration Wizard

Use the interactive migration wizard:

```bash
php artisan module:migrate Blog --source=/path/to/external-project
```

The wizard will guide you through:
- Namespace updates
- Route migration
- Asset handling
- Shared model identification

### Step 3: Manual Steps

After migration, you may need to:

1. **Update Namespaces** (if not done automatically):
   ```bash
   php artisan module:namespace:update Blog --from="App\\" --to="Modules\\Blog\\"
   ```

2. **Register Assets**:
   ```bash
   php artisan module:assets:discover --update-vite
   ```

3. **Generate Route Helpers**:
   ```bash
   php artisan module:route:helper Blog
   ```

4. **Update Dependencies**:
   - Review `composer.json` in module
   - Update any shared dependencies

### Step 4: Verify Migration

Check module health:

```bash
php artisan module:health Blog
```

## Common Migration Scenarios

### Scenario 1: Simple Project Migration

```bash
# 1. Create module
php artisan module:make Blog

# 2. Migrate
php artisan module:migrate Blog --source=/path/to/blog-project

# 3. Verify
php artisan module:health Blog
```

### Scenario 2: Namespace Update Only

If you just need to update namespaces:

```bash
php artisan module:namespace:update Blog \
    --from="App\\" \
    --to="Modules\\Blog\\"
```

### Scenario 3: Route Migration

Update route names to match module patterns:

```bash
# Manual route updates in files
# Or use migration wizard which handles this
```

## Handling Shared Models

If your external project uses models that should be shared:

1. **Identify Shared Models**:
   - User, Role, Permission models
   - Common domain models

2. **Update Imports**:
   ```php
   // Before
   use App\Models\User;
   
   // After
   use App\Models\User; // Keep using main project model
   ```

3. **Don't Copy Shared Models**:
   - Keep them in main project
   - Reference them from module

## Asset Migration

### Option 1: Copy Assets

Assets are copied to module directory:

```
Modules/Blog/resources/css/
Modules/Blog/resources/js/
```

### Option 2: Link Assets

Create symlinks to original assets (manual process).

### Option 3: Centralize Assets

Use main project's Vite config:

```bash
php artisan module:assets:discover --update-vite
```

## Route Migration

Routes need to be updated to match module patterns:

### Before Migration

```php
route('filament.app.pages.dashboard')
```

### After Migration

```php
route('filament.blog-admin.pages.dashboard')
```

Use route helpers to avoid hardcoding:

```bash
php artisan module:route:helper Blog
```

Then use:

```php
use Modules\Blog\Helpers\RouteHelper;

RouteHelper::adminPage('dashboard');
```

## Post-Migration Checklist

- [ ] Namespaces updated
- [ ] Routes migrated
- [ ] Assets registered
- [ ] Shared models identified
- [ ] Dependencies updated
- [ ] Tests updated
- [ ] Documentation updated
- [ ] Health check passed

## Troubleshooting

### Namespace Issues

```bash
# Check for remaining namespaces
php artisan module:health Blog

# Update manually if needed
php artisan module:namespace:update Blog --from="App\\" --to="Modules\\Blog\\"
```

### Route Not Found

```bash
# Generate route helpers
php artisan module:route:helper Blog

# Check route names
php artisan route:list | grep blog
```

### Asset Issues

```bash
# Discover and register assets
php artisan module:assets:discover --update-vite

# Rebuild assets
npm run build
```

## See Also

- [Module Migrate Command](../commands/module-migrate.md)
- [Module Validate Command](../commands/module-validate.md)
- [Module Namespace Update Command](../commands/module-namespace-update.md)
- [Troubleshooting](troubleshooting.md)

