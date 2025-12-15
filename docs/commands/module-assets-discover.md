# module:assets:discover

Discover and optionally register module assets in Vite configuration.

## Usage

```bash
php artisan module:assets:discover [--update-vite] [--dry-run]
```

## Options

- `--update-vite` - Automatically update `vite.config.js` with discovered assets
- `--dry-run` - Show what would be done without making changes

## Description

Scans all modules for CSS and JavaScript files and optionally adds them to your main `vite.config.js` file. This helps centralize asset compilation.

## Examples

### Discover Assets (Preview Only)

```bash
php artisan module:assets:discover
```

### Discover and Update Vite Config

```bash
php artisan module:assets:discover --update-vite
```

### Dry Run (Preview Changes)

```bash
php artisan module:assets:discover --update-vite --dry-run
```

## Asset Paths

By default, the command looks for:

- CSS files: `resources/css/**/*.css`
- JS files: `resources/js/**/*.js`

You can configure these paths in `config/filament-modules.php`:

```php
'asset_discovery' => [
    'css_paths' => ['resources/css/**/*.css'],
    'js_paths' => ['resources/js/**/*.js'],
],
```

## Output

The command displays:

- List of discovered CSS files
- List of discovered JS files
- Total asset count
- Vite config updates (if `--update-vite` is used)

## See Also

- [Configuration](../configuration.md#asset-discovery-configuration)
- [Getting Started](../guides/getting-started.md)

