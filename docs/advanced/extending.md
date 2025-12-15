# Extending the Package

How to extend Filament Modules functionality.

## Custom Commands

Create custom commands that extend module functionality:

```php
namespace Modules\Blog\Commands;

use Illuminate\Console\Command;

class BlogCustomCommand extends Command
{
    protected $signature = 'blog:custom';
    
    public function handle()
    {
        // Custom logic
    }
}
```

## Custom Generators

Extend the package generators:

```php
use Coolsam\Modules\Commands\FileGenerators\ModulePanelProviderClassGenerator;

class CustomPanelGenerator extends ModulePanelProviderClassGenerator
{
    // Override methods to customize generation
}
```

## Custom Stubs

Publish and customize stubs:

```bash
php artisan vendor:publish --tag="modules-stubs"
```

Modify stubs in `resources/vendor/modules/`.

## Service Provider Extensions

Extend the ModulesServiceProvider:

```php
use Coolsam\Modules\ModulesServiceProvider;

class CustomModulesServiceProvider extends ModulesServiceProvider
{
    // Override methods to customize behavior
}
```

## Plugin Extensions

Extend the ModulesPlugin:

```php
use Coolsam\Modules\ModulesPlugin;

class CustomModulesPlugin extends ModulesPlugin
{
    // Override methods to customize plugin behavior
}
```

## See Also

- [Customization](customization.md)
- [Architecture](architecture.md)

