# Customization

Advanced customization options for Filament Modules.

## Custom Panel Configuration

### Custom Panel IDs

Configure panel ID patterns:

```php
'module_panel' => [
    'panel_id_pattern' => '{module-slug}-{panel-name}',
],
```

### Custom Route Prefixes

```php
'module_panel' => [
    'route_prefix_pattern' => '{panel-id}',
],
```

### Custom Path Strategies

```php
'module_panel' => [
    'path_strategy' => 'module_only', // or 'module_prefix_with_id', 'panel_id_only'
],
```

## Extending Generators

You can extend the package generators by:

1. Publishing stubs:
   ```bash
   php artisan vendor:publish --tag="modules-stubs"
   ```

2. Modifying stub files in `resources/vendor/modules/`

3. Creating custom generators

## Custom Service Providers

Add custom service provider logic:

```php
// Modules/Blog/app/Providers/BlogServiceProvider.php
public function boot()
{
    // Custom boot logic
    $this->loadViewsFrom(
        module_path('Blog', 'resources/views'),
        'blog'
    );
    
    // Register custom routes
    $this->loadRoutesFrom(
        module_path('Blog', 'routes/web.php')
    );
}
```

## Access Control

Use the provided access control classes:

```php
use Coolsam\Modules\Resource;
use Coolsam\Modules\Page;
use Coolsam\Modules\Traits\CanAccessTrait;
```

## Custom Middleware

Add custom middleware to panels:

```php
// In panel provider
->middleware([
    CustomMiddleware::class,
])
```

## See Also

- [Extending the Package](extending.md)
- [Architecture](architecture.md)

