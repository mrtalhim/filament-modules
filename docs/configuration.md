# Configuration

The Filament Modules package can be configured via the `config/filament-modules.php` configuration file. This document describes all available configuration options.

## Configuration File

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-modules-config"
```

The configuration file will be located at `config/filament-modules.php`.

## Configuration Options

### Mode

**Key**: `mode`

**Type**: `string` (enum value)

**Default**: `ConfigMode::BOTH->value`

**Description**: The mode used by the package to discover and register resources from modules.

**Options**:
- `plugins` - Only register plugins from modules
- `panels` - Only register panels from modules  
- `both` - Register both plugins and panels (default)

**Example**:
```php
'mode' => \Coolsam\Modules\Enums\ConfigMode::BOTH->value,
```

### Auto-Register Plugins

**Key**: `auto-register-plugins`

**Type**: `boolean`

**Default**: `true`

**Description**: If set to `true`, the package will automatically register all plugins found in your modules. Otherwise, you will need to register each plugin manually in your Filament Panel.

**Example**:
```php
'auto-register-plugins' => true,
```

### Auto-Register Panels

**Key**: `auto_register_panels`

**Type**: `boolean`

**Default**: `true`

**Description**: Whether to auto-register module panel providers. When enabled, panel providers are automatically discovered and registered.

**Example**:
```php
'auto_register_panels' => true,
```

## Clusters Configuration

### Enable Clusters

**Key**: `clusters.enabled`

**Type**: `boolean`

**Default**: `true`

**Description**: If set to `true`, a cluster will be created in each module during the `module:filament:install` command and all Filament files for that module may reside inside that cluster. Otherwise, Filament files will reside in `Filament/Resources`, `Filament/Pages`, `Filament/Widgets`, etc.

**Example**:
```php
'clusters' => [
    'enabled' => true,
],
```

### Use Top Navigation

**Key**: `clusters.use-top-navigation`

**Type**: `boolean`

**Default**: `true`

**Description**: If set to `true`, the top navigation will be used to navigate between clusters while the actual links will be loaded as a side sub-navigation. This improves UX. Otherwise, the package will honor the configuration that you have in your panel.

**Example**:
```php
'clusters' => [
    'use-top-navigation' => true,
],
```

## Panels Configuration

### Navigation Group

**Key**: `panels.group`

**Type**: `string`

**Default**: `'Panels'`

**Description**: The group under which the panels will be registered in the Main Panel's navigation. This is only applicable if the mode is set to support panels. All links to the various module panels will be grouped under this group in the main panel's navigation.

**Example**:
```php
'panels' => [
    'group' => 'Module Panels',
],
```

### Group Icon

**Key**: `panels.group-icon`

**Type**: `string` (Heroicon name)

**Default**: `\Filament\Support\Icons\Heroicon::OutlinedRectangleStack`

**Description**: The group icon used in the navigation. This is only applicable if the mode is set to support panels.

**Example**:
```php
'panels' => [
    'group-icon' => \Filament\Support\Icons\Heroicon::OutlinedRectangleStack,
],
```

### Open in New Tab

**Key**: `panels.open-in-new-tab`

**Type**: `boolean`

**Default**: `false`

**Description**: If set to `true`, the links to the module panels will open in a new tab. This is only applicable if the mode is set to support panels.

**Example**:
```php
'panels' => [
    'open-in-new-tab' => false,
],
```

### Group Sort Order

**Key**: `panels.group-sort`

**Type**: `integer`

**Default**: `0`

**Description**: The sort order applied on each navigation item in the modules panel group.

**Example**:
```php
'panels' => [
    'group-sort' => 0,
],
```

### Back to Main Label

**Key**: `panels.back_to_main_label`

**Type**: `string`

**Default**: `'Back to Admin'`

**Description**: The label for the "back to main panel" navigation item shown in module panels.

**Example**:
```php
'panels' => [
    'back_to_main_label' => '← Back to Admin',
],
```

### Back to Main Icon

**Key**: `panels.back_to_main_icon`

**Type**: `string` (Heroicon name)

**Default**: `'heroicon-o-arrow-left'`

**Description**: The icon for the "back to main panel" navigation item.

**Example**:
```php
'panels' => [
    'back_to_main_icon' => 'heroicon-o-arrow-left',
],
```

### Back to Main URL

**Key**: `panels.back_to_main_url`

**Type**: `string`

**Default**: `'/admin'`

**Description**: The URL to redirect to when clicking the back button (typically your main admin panel URL).

**Example**:
```php
'panels' => [
    'back_to_main_url' => '/admin',
],
```

### Require Authentication

**Key**: `panels.require_auth`

**Type**: `boolean`

**Default**: `true`

**Description**: Whether module panels should enforce authentication. When enabled, unauthenticated users are redirected to the main panel.

**Example**:
```php
'panels' => [
    'require_auth' => true,
],
```

## Module Panel Configuration

### Default Panel ID

**Key**: `module_panel.default_id`

**Type**: `string`

**Default**: `'admin'`

**Description**: The default ID for a module's Filament panel.

**Example**:
```php
'module_panel' => [
    'default_id' => 'admin',
],
```

### Path Strategy

**Key**: `module_panel.path_strategy`

**Type**: `string`

**Default**: `'module_only'`

**Description**: How the URL path is constructed for module panels.

**Options**:
- `module_prefix_with_id` - Path includes module name and panel ID (e.g., `/blog/admin`)
- `module_only` - Path uses only module name (e.g., `/blog`)
- `panel_id_only` - Path uses only panel ID (e.g., `/admin`)

**Example**:
```php
'module_panel' => [
    'path_strategy' => 'module_only',
],
```

### Auto Create on Install

**Key**: `module_panel.auto_create_on_install`

**Type**: `boolean`

**Default**: `true`

**Description**: Whether `module:filament:install` automatically creates a panel.

**Example**:
```php
'module_panel' => [
    'auto_create_on_install' => true,
],
```

### Panel ID Pattern

**Key**: `module_panel.panel_id_pattern`

**Type**: `string`

**Default**: `'{module-slug}-{panel-name}'`

**Description**: Pattern for generating panel IDs.

**Placeholders**:
- `{module-slug}` - Module name in kebab-case
- `{panel-name}` - Panel name in kebab-case

**Example**:
```php
'module_panel' => [
    'panel_id_pattern' => '{module-slug}-{panel-name}',
],
```

### Route Prefix Pattern

**Key**: `module_panel.route_prefix_pattern`

**Type**: `string`

**Default**: `'{panel-id}'`

**Description**: Pattern for generating route prefixes.

**Placeholders**:
- `{panel-id}` - Full panel ID
- `{module-slug}` - Module name in kebab-case

**Example**:
```php
'module_panel' => [
    'route_prefix_pattern' => '{panel-id}',
],
```

## Module Generator Configuration

### Include Gitignore

**Key**: `module_generator.include_gitignore`

**Type**: `boolean`

**Default**: `true`

**Description**: Whether to generate `.gitignore` file when installing Filament.

**Example**:
```php
'module_generator' => [
    'include_gitignore' => true,
],
```

### Include README

**Key**: `module_generator.include_readme`

**Type**: `boolean`

**Default**: `true`

**Description**: Whether to generate `README.md` file when installing Filament.

**Example**:
```php
'module_generator' => [
    'include_readme' => true,
],
```

### Auto Register Views

**Key**: `module_generator.auto_register_views`

**Type**: `boolean`

**Default**: `true`

**Description**: Whether to auto-register view namespaces in service provider.

**Example**:
```php
'module_generator' => [
    'auto_register_views' => true,
],
```

### Generate Route Helpers

**Key**: `module_generator.generate_route_helpers`

**Type**: `boolean`

**Default**: `false`

**Description**: Whether to automatically generate route helper traits.

**Example**:
```php
'module_generator' => [
    'generate_route_helpers' => false,
],
```

## Asset Discovery Configuration

### Enabled

**Key**: `asset_discovery.enabled`

**Type**: `boolean`

**Default**: `true`

**Description**: Whether asset discovery is enabled.

**Example**:
```php
'asset_discovery' => [
    'enabled' => true,
],
```

### Auto Add to Vite

**Key**: `asset_discovery.auto_add_to_vite`

**Type**: `boolean`

**Default**: `false`

**Description**: Whether to automatically update `vite.config.js` with discovered assets.

**Example**:
```php
'asset_discovery' => [
    'auto_add_to_vite' => false,
],
```

### CSS Paths

**Key**: `asset_discovery.css_paths`

**Type**: `array`

**Default**: `['resources/css/**/*.css']`

**Description**: Glob patterns for CSS files.

**Example**:
```php
'asset_discovery' => [
    'css_paths' => ['resources/css/**/*.css'],
],
```

### JS Paths

**Key**: `asset_discovery.js_paths`

**Type**: `array`

**Default**: `['resources/js/**/*.js']`

**Description**: Glob patterns for JS files.

**Example**:
```php
'asset_discovery' => [
    'js_paths' => ['resources/js/**/*.js'],
],
```

## Complete Configuration Example

```php
<?php

return [
    'mode' => \Coolsam\Modules\Enums\ConfigMode::BOTH->value,
    'auto-register-plugins' => true,
    'auto_register_panels' => true,

    'clusters' => [
        'enabled' => true,
        'use-top-navigation' => true,
    ],

    'panels' => [
        'group' => 'Module Panels',
        'group-icon' => \Filament\Support\Icons\Heroicon::OutlinedRectangleStack,
        'group-sort' => 0,
        'open-in-new-tab' => false,
        'back_to_main_label' => 'Back to Admin',
        'back_to_main_icon' => 'heroicon-o-arrow-left',
        'back_to_main_url' => '/admin',
        'require_auth' => true,
    ],

    'module_panel' => [
        'default_id' => 'admin',
        'path_strategy' => 'module_only',
        'auto_create_on_install' => true,
        'skip_nwidart_defaults' => true,
        'panel_id_pattern' => '{module-slug}-{panel-name}',
        'route_prefix_pattern' => '{panel-id}',
    ],

    'module_generator' => [
        'include_gitignore' => true,
        'include_readme' => true,
        'auto_register_views' => true,
        'generate_route_helpers' => false,
    ],

    'asset_discovery' => [
        'enabled' => true,
        'auto_add_to_vite' => false,
        'css_paths' => ['resources/css/**/*.css'],
        'js_paths' => ['resources/js/**/*.js'],
    ],
];
```

