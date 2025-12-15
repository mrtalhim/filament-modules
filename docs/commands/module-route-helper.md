# module:route:helper

Generate route helper trait for a module.

## Usage

```bash
php artisan module:route:helper {module} [--panel=panel-id]
```

## Arguments

- `module` - The name of the module

## Options

- `--panel` - Specific panel ID to generate helpers for (optional)

## Description

Generates a `RouteHelper` trait with methods for generating routes to module panels, pages, and resources. This helps avoid hardcoded route names and provides type-safe route generation.

## Examples

### Generate Helpers for All Panels

```bash
php artisan module:route:helper Blog
```

### Generate Helpers for Specific Panel

```bash
php artisan module:route:helper Blog --panel=blog-admin
```

## Generated File

Creates a trait at:

```
Modules/{Module}/app/Helpers/RouteHelper.php
```

## Generated Methods

For each panel, the trait includes:

- `{panel}Page(string $page)` - Get route for panel pages
- `{panel}Resource(string $resource, string $action)` - Get route for resources
- `{panel}ResourceCreate(string $resource)` - Get route for resource creation
- `{panel}ResourceEdit(string $resource, $record)` - Get route for resource editing
- `{panel}ResourceView(string $resource, $record)` - Get route for resource viewing

## Usage Example

```php
use Modules\Blog\Helpers\RouteHelper;

class SomeController
{
    use RouteHelper;

    public function redirectToBlog()
    {
        return redirect(self::adminPage('dashboard'));
    }

    public function editPost($post)
    {
        return redirect(self::adminResourceEdit('posts', $post));
    }
}
```

## See Also

- [Configuration](../configuration.md)
- [Getting Started](../guides/getting-started.md)

