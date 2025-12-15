# Commands Reference

Filament Modules provides a comprehensive set of Artisan commands to help you manage modules and generate Filament components.

## Installation & Setup Commands

### module:filament:install
Set up Filament support in a module.

**See**: [Module Filament Install](module-filament-install.md)

## Generation Commands

### module:make:filament-panel
Create a new Filament panel in a module.

**See**: [Create Panel](module-make-filament-panel.md)

### module:make:filament-resource
Create a new Filament resource in a module.

**See**: [Create Resource](module-make-filament-resource.md)

### module:make:filament-page
Create a new Filament page in a module.

**See**: [Create Page](module-make-filament-page.md)

### module:make:filament-widget
Create a new Filament widget in a module.

**See**: [Create Widget](module-make-filament-widget.md)

### module:make:filament-cluster
Create a new Filament cluster in a module.

**See**: [Create Cluster](module-make-filament-cluster.md)

### module:make:filament-plugin
Create a new Filament plugin in a module.

**See**: [Create Plugin](module-make-filament-plugin.md)

### module:make:filament-theme
Create a new Filament theme in a module.

**See**: [Create Theme](module-make-filament-theme.md)

## Management Commands

### module:namespace:update
Update namespaces in a module from one pattern to another.

**See**: [Namespace Update](module-namespace-update.md)

### module:assets:discover
Discover and optionally register module assets in Vite configuration.

**See**: [Assets Discovery](module-assets-discover.md)

### module:validate
Validate external project compatibility before module integration.

**See**: [Module Validation](module-validate.md)

### module:migrate
Migrate external Filament project into a module with interactive wizard.

**See**: [Module Migration](module-migrate.md)

### module:health
Check the health of a module and identify potential issues.

**See**: [Module Health](module-health.md)

### module:route:helper
Generate route helper trait for a module.

**See**: [Route Helper](module-route-helper.md)

## Command Aliases

Many commands have shorter aliases for convenience:

| Full Command | Aliases |
|--------------|---------|
| `module:make:filament-resource` | `module:filament:resource`, `module:filament:make-resource` |
| `module:make:filament-page` | `module:filament:page`, `module:filament:make-page` |
| `module:make:filament-widget` | `module:filament:widget`, `module:filament:make-widget` |
| `module:make:filament-cluster` | `module:filament:cluster`, `module:filament:make-cluster` |
| `module:make:filament-plugin` | `module:filament:plugin`, `module:filament:make-plugin` |
| `module:make:filament-theme` | `module:filament:theme`, `module:filament:make-theme` |
| `module:make:filament-panel` | `module:filament:panel`, `module:filament:make-panel` |

## Quick Examples

### Basic Workflow

```bash
# 1. Create a module
php artisan module:make Blog

# 2. Install Filament support
php artisan module:filament:install Blog

# 3. Create a resource
php artisan module:make:filament-resource Post Blog --model

# 4. Create a page
php artisan module:make:filament-page Dashboard Blog

# 5. Check module health
php artisan module:health Blog
```

### Advanced Workflow

```bash
# Update namespaces
php artisan module:namespace:update Blog --from="App\\" --to="Modules\\Blog\\"

# Discover assets
php artisan module:assets:discover --update-vite

# Generate route helpers
php artisan module:route:helper Blog

# Migrate external project
php artisan module:migrate Blog --source=/path/to/project
```

## Interactive Commands

Most commands support interactive mode, prompting you for required information:

```bash
# Interactive resource creation
php artisan module:make:filament-resource

# Follow the prompts:
# - What should the resource be named?
# - In which Module should we create this?
# - Which model should this resource use?
```

## Getting Help

For detailed information about any command, use the `--help` flag:

```bash
php artisan module:filament:install --help
php artisan module:make:filament-resource --help
```

