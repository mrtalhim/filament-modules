# Troubleshooting

Common issues and solutions when working with Filament Modules.

## Module Not Found

**Issue**: `Module 'X' not found`

**Solutions**:
1. Verify module exists: `php artisan module:list`
2. Check module is enabled: `php artisan module:enable X`
3. Run autoload: `composer dump-autoload`

## Panels Not Appearing

**Issue**: Module panels don't appear in navigation

**Solutions**:
1. Check configuration mode:
   ```php
   'mode' => \Coolsam\Modules\Enums\ConfigMode::PANELS->value
   ```

2. Verify panel provider exists:
   ```
   Modules/X/app/Providers/Filament/XPanelProvider.php
   ```

3. Check auto-registration:
   ```php
   'auto_register_panels' => true
   ```

4. Run health check:
   ```bash
   php artisan module:health X
   ```

## Plugins Not Loading

**Issue**: Plugins not being registered

**Solutions**:
1. Check configuration:
   ```php
   'mode' => \Coolsam\Modules\Enums\ConfigMode::PLUGINS->value,
   'auto-register-plugins' => true,
   ```

2. Verify plugin file exists:
   ```
   Modules/X/app/Filament/XPlugin.php
   ```

3. Check namespace matches module

## Windows Path Issues

**Issue**: Path separator problems on Windows

**Solutions**:
- This is fixed in v5.x
- Ensure you're using the latest version
- Path normalization is automatic

## Namespace Conflicts

**Issue**: Namespace errors or conflicts

**Solutions**:
1. Update namespaces:
   ```bash
   php artisan module:namespace:update X --from="App\\" --to="Modules\\X\\"
   ```

2. Check for conflicts:
   ```bash
   php artisan module:validate /path/to/project
   ```

## Routes Not Working

**Issue**: Routes return 404

**Solutions**:
1. Check route registration:
   ```bash
   php artisan route:list | grep module-name
   ```

2. Verify panel provider is registered

3. Clear route cache:
   ```bash
   php artisan route:clear
   ```

## View Not Found

**Issue**: Views not loading

**Solutions**:
1. Check view registration in service provider:
   ```php
   $this->loadViewsFrom(
       module_path('X', 'resources/views'),
       'x'
   );
   ```

2. Verify view namespace:
   ```php
   view('x::path.to.view')
   ```

3. Run health check:
   ```bash
   php artisan module:health X
   ```

## Assets Not Loading

**Issue**: CSS/JS files not compiling

**Solutions**:
1. Discover assets:
   ```bash
   php artisan module:assets:discover --update-vite
   ```

2. Check Vite config includes module assets

3. Rebuild assets:
   ```bash
   npm run build
   ```

## Authentication Issues

**Issue**: Can't access module panels

**Solutions**:
1. Check authentication configuration:
   ```php
   'panels' => [
       'require_auth' => true,
   ]
   ```

2. Verify main panel authentication works

3. Check middleware is applied

## Module Health Check

Run comprehensive health check:

```bash
php artisan module:health ModuleName
```

This checks:
- Namespace consistency
- Route validity
- View registration
- Asset configuration
- Service provider setup
- Panel configuration

## Auto-Fix Issues

Try auto-fixing:

```bash
php artisan module:health ModuleName --fix
```

## Common Configuration Mistakes

### Wrong Mode

```php
// Wrong - using plugins mode but creating panels
'mode' => \Coolsam\Modules\Enums\ConfigMode::PLUGINS->value,

// Correct
'mode' => \Coolsam\Modules\Enums\ConfigMode::PANELS->value,
```

### Missing Plugin Registration

```php
// Missing in panel provider
->plugin(ModulesPlugin::make())
```

### Incorrect Path Strategy

```php
// Check path strategy matches your needs
'path_strategy' => 'module_only', // or 'module_prefix_with_id'
```

## Getting Help

1. Check [Configuration](../configuration.md)
2. Review [Commands Reference](../commands/index.md)
3. Run health check: `php artisan module:health ModuleName`
4. Check module structure matches expected format

## See Also

- [Getting Started](getting-started.md)
- [Configuration](../configuration.md)
- [Module Health Command](../commands/module-health.md)

