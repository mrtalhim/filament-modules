# Getting Started

This guide will walk you through creating your first Filament module from scratch.

## Prerequisites

- Laravel 11.x or 12.x installed
- Filament 4.x installed and configured
- nwidart/laravel-modules configured

## Quick Start

### 1. Install Filament Modules

```bash
composer require coolsam/modules
```

### 2. Register the Plugin

In your Filament panel provider:

```php
use Coolsam\Modules\ModulesPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(ModulesPlugin::make());
}
```

### 3. Create Your First Module

```bash
# Create a module
php artisan module:make Blog

# Install Filament support
php artisan module:filament:install Blog
```

### 4. Create a Resource

```bash
php artisan module:make:filament-resource Post Blog --model
```

### 5. Access Your Module

- If using panels mode: Visit `/blog` (or your configured path)
- If using plugins mode: Access via main admin panel

## Complete Example: Blog Module

Let's create a complete blog module with posts and categories.

### Step 1: Create the Module

```bash
php artisan module:make Blog
```

### Step 2: Install Filament

```bash
php artisan module:filament:install Blog
```

Follow the prompts:
- Use clusters? (Yes/No)
- Create default cluster? (Yes/No)
- Frontend preset? (Tailwind/Sass)

### Step 3: Create Models and Resources

```bash
# Create Post resource with model
php artisan module:make:filament-resource Post Blog --model

# Create Category resource with model
php artisan module:make:filament-resource Category Blog --model
```

### Step 4: Create a Dashboard Page

```bash
php artisan module:make:filament-page Dashboard Blog
```

### Step 5: Create a Widget

```bash
php artisan module:make:filament-widget RecentPosts Blog --stats-overview
```

### Step 6: Configure Relationships

Edit your models to add relationships:

```php
// Modules/Blog/app/Models/Post.php
public function category()
{
    return $this->belongsTo(Category::class);
}
```

### Step 7: Access Your Module

Visit your module panel at `/blog` (or configured path) and start managing your blog!

## Next Steps

- Read about [Creating Modules](creating-modules.md) for advanced workflows
- Learn about [Panels vs Plugins](panels-vs-plugins.md) to choose the right approach
- Check the [Commands Reference](../commands/index.md) for all available commands
- Review [Configuration](../configuration.md) for customization options

## Common Workflows

### Creating a New Resource

```bash
php artisan module:make:filament-resource Product Shop --model
```

### Adding a Page

```bash
php artisan module:make:filament-page Reports Analytics
```

### Creating Multiple Panels

```bash
php artisan module:make:filament-panel admin Blog
php artisan module:make:filament-panel member Blog
```

## Troubleshooting

If you encounter issues:

1. Check [Troubleshooting Guide](troubleshooting.md)
2. Run `php artisan module:health {ModuleName}` to check module health
3. Verify configuration in `config/filament-modules.php`

## See Also

- [Installation](../installation.md)
- [Configuration](../configuration.md)
- [Commands Reference](../commands/index.md)

