# Architecture

Overview of Filament Modules package architecture.

## Core Components

### ModulesPlugin

The main plugin that handles:
- Plugin discovery and registration
- Panel discovery and navigation
- Module integration

### ModulesServiceProvider

Service provider that:
- Discovers module service providers
- Registers panel providers
- Handles module bootstrapping

### Commands

Command classes for:
- Module scaffolding
- Component generation
- Module management

## File Structure

```
src/
  Commands/          # Artisan commands
  Concerns/          # Shared traits
  Enums/            # Configuration enums
  Facades/          # Facade classes
  FileGenerators/   # Code generators
  Http/             # HTTP middleware
  Traits/           # Utility traits
```

## Discovery Process

1. **Plugin Discovery**: Scans `Modules/*/app/Filament/*Plugin.php`
2. **Panel Discovery**: Scans `Modules/*/app/Providers/Filament/*PanelProvider.php`
3. **Auto-Registration**: Registers discovered components

## Configuration Flow

1. Load `config/filament-modules.php`
2. Determine mode (plugins/panels/both)
3. Discover components based on mode
4. Register with Filament

## See Also

- [Extending the Package](extending.md)
- [Customization](customization.md)

