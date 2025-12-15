# Panels vs Plugins

Understanding when to use panels versus plugins in Filament Modules.

## Overview

Filament Modules supports two modes of organization:

- **Plugins**: Extend functionality of your main Filament panel
- **Panels**: Create independent Filament panels with separate URLs

## Plugins Mode

Plugins extend your main Filament panel with additional functionality.

### When to Use Plugins

- ✅ Adding features to existing admin panel
- ✅ Sharing functionality across multiple panels
- ✅ Creating reusable components
- ✅ Extending Filament with custom functionality

### Example Use Cases

- Analytics dashboard widget
- Notification system
- Custom form components
- Report generators

### Configuration

```php
// config/filament-modules.php
'mode' => \Coolsam\Modules\Enums\ConfigMode::PLUGINS->value,
'auto-register-plugins' => true,
```

### Creating a Plugin

```bash
php artisan module:make:filament-plugin AnalyticsPlugin Analytics
```

## Panels Mode

Panels create independent Filament panels with their own URLs and navigation.

### When to Use Panels

- ✅ Separate admin areas (e.g., admin vs member)
- ✅ Multi-tenant applications
- ✅ Different user roles with different interfaces
- ✅ Standalone modules that need isolation

### Example Use Cases

- Member portal (`/member`)
- Blog admin (`/blog`)
- Analytics dashboard (`/analytics`)
- Customer portal (`/customer`)

### Configuration

```php
// config/filament-modules.php
'mode' => \Coolsam\Modules\Enums\ConfigMode::PANELS->value,
'panels' => [
    'group' => 'Module Panels',
    'require_auth' => true,
    'back_to_main_url' => '/admin',
],
```

### Creating a Panel

```bash
php artisan module:make:filament-panel admin Blog
```

## Both Mode (Default)

You can use both plugins and panels simultaneously:

```php
'mode' => \Coolsam\Modules\Enums\ConfigMode::BOTH->value,
```

This allows:
- Plugins to extend main panel
- Panels for separate admin areas
- Maximum flexibility

## Comparison

| Feature | Plugins | Panels |
|---------|---------|--------|
| URL | Main panel URL | Separate URL (`/module-name`) |
| Navigation | Integrated | Independent |
| Authentication | Shared | Centralized (inherits main) |
| Isolation | Low | High |
| Use Case | Extend functionality | Separate admin areas |

## Decision Guide

### Choose Plugins If:

- You want to add features to existing admin
- Components should be reusable
- Everything belongs in one admin interface
- You're creating widgets/components

### Choose Panels If:

- You need separate admin areas
- Different user roles need different interfaces
- Modules should be isolated
- You want dedicated URLs per module

### Choose Both If:

- You want maximum flexibility
- Some modules extend main panel, others are separate
- You're building a complex multi-tenant system

## Examples

### E-commerce Application

```php
// Main admin panel (plugins)
- Orders plugin
- Products plugin
- Customers plugin

// Separate panels
- Vendor panel (`/vendor`) - for vendors
- Customer panel (`/customer`) - for customers
```

### Content Management

```php
// Main admin (plugins)
- Analytics plugin
- SEO plugin

// Separate panels
- Blog panel (`/blog`) - blog management
- Media panel (`/media`) - media library
```

## Migration Between Modes

You can change modes at any time:

1. Update `config/filament-modules.php`
2. Run `php artisan config:clear`
3. Regenerate components if needed

## See Also

- [Configuration](../configuration.md)
- [Getting Started](getting-started.md)
- [Creating Modules](creating-modules.md)

