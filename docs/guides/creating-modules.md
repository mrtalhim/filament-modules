# Creating Modules

A comprehensive guide to creating and organizing Filament modules.

## Module Structure

After installing Filament in a module, you'll have this structure:

```
Modules/
  MyModule/
    app/
      Filament/
        Clusters/          # If clusters enabled
          MyModule/
            Pages/
            Resources/
            Widgets/
        Pages/             # If clusters disabled
        Resources/
        Widgets/
        MyModulePlugin.php
      Providers/
        Filament/
          MyModulePanelProvider.php
        MyModuleServiceProvider.php
    resources/
      css/
      js/
      views/
    .gitignore
    README.md
    CHANGELOG.md
```

## Workflow Options

### Option 1: Clusters (Recommended for Complex Modules)

Clusters organize related resources, pages, and widgets together:

```bash
php artisan module:filament:install Blog --cluster
```

**Use clusters when:**
- Module has multiple functional areas
- You want clear separation of concerns
- Module will grow significantly

### Option 2: Standard Structure

Standard Filament structure without clusters:

```bash
php artisan module:filament:install Blog
```

**Use standard structure when:**
- Module is simple
- You prefer flat organization
- Module won't grow much

## Creating Components

### Resources

```bash
# Basic resource
php artisan module:make:filament-resource Post Blog

# With model
php artisan module:make:filament-resource Post Blog --model

# With view page
php artisan module:make:filament-resource Post Blog --model --view
```

### Pages

```bash
# Dashboard page
php artisan module:make:filament-page Dashboard Blog

# Settings page
php artisan module:make:filament-page Settings Blog --type=settings
```

### Widgets

```bash
# Stats widget
php artisan module:make:filament-widget BlogStats Blog --stats-overview

# Chart widget
php artisan module:make:filament-widget PostChart Blog --chart

# Table widget
php artisan module:make:filament-widget RecentPosts Blog --table
```

## Multiple Panels

You can create multiple panels in a single module:

```bash
# Admin panel
php artisan module:make:filament-panel admin Blog

# Member panel
php artisan module:make:filament-panel member Blog

# Public panel
php artisan module:make:filament-panel public Blog
```

Each panel will have:
- Unique URL path
- Separate navigation
- Independent resources/pages/widgets

## Best Practices

### 1. Use Descriptive Names

```bash
# Good
php artisan module:make Blog
php artisan module:make:filament-resource Post Blog

# Avoid
php artisan module:make M1
php artisan module:make:filament-resource P Blog
```

### 2. Organize by Functionality

Group related resources in clusters:

```bash
# Blog module with clusters
- Posts cluster (Posts, Categories, Tags)
- Comments cluster (Comments, Moderation)
- Analytics cluster (Reports, Stats)
```

### 3. Use Models Properly

```bash
# Create model with resource
php artisan module:make:filament-resource Post Blog --model

# Or use existing model
php artisan module:make:filament-resource Post Blog --model-fqn="App\Models\Post"
```

### 4. Register Views

Views are automatically registered, but you can customize:

```php
// In ModuleServiceProvider
public function boot()
{
    $this->loadViewsFrom(
        module_path('Blog', 'resources/views'),
        'blog'
    );
}
```

## Module Health

Regularly check module health:

```bash
php artisan module:health Blog
```

This checks:
- Namespace consistency
- Route validity
- View registration
- Asset configuration
- Service provider setup

## Sharing Modules

To share modules between projects:

1. Package as Composer package
2. Use Git submodules
3. Copy module directory

## See Also

- [Getting Started](getting-started.md)
- [Panels vs Plugins](panels-vs-plugins.md)
- [Commands Reference](../commands/index.md)

