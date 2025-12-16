# module:filament:install

Set up Filament support in a module by generating the necessary files and directories.

## Usage

```bash
php artisan module:filament:install {module}
```

## Arguments

- `module` - The name of the module in which to install Filament support

## Options

- `--cluster`, `-C` - Organize code into Filament clusters
- `--sass` - Use Sass instead of Tailwind CSS
- `--create-default-cluster` - Create a default cluster for the module (only used when `--cluster` is set)
- `--no-interaction`, `-n` - Run in non-interactive mode (no prompts)

## Description

This command prepares your module for Filament by:

- Creating Filament directory structure
- Setting up frontend scaffolding (Tailwind or Sass)
- Generating a default Filament plugin (if plugins mode is enabled)
- Creating a default Filament panel (if panels mode is enabled)
- Registering view namespaces in the service provider
- Generating `.gitignore`, `README.md`, and `CHANGELOG.md` files

## Examples

### Basic Installation

```bash
php artisan module:filament:install Blog
```

### With Clusters

```bash
php artisan module:filament:install Blog --cluster
```

### With Sass

```bash
php artisan module:filament:install Blog --sass
```

### Non-Interactive Mode

```bash
# Install with clusters and create default cluster
php artisan module:filament:install Blog --cluster --create-default-cluster --no-interaction

# Install with Sass in non-interactive mode
php artisan module:filament:install Blog --sass --no-interaction
```

## Interactive Mode

When run interactively, the command will prompt you:

- Whether to organize code into clusters
- Whether to create a default cluster
- Frontend preset selection (Tailwind or Sass)

## Non-Interactive Mode

When run with `--no-interaction` or in a CI/CD environment (`CI=true`), the command will:

- Use `--cluster` flag value (defaults to `false` if not provided)
- Use `--create-default-cluster` flag value (defaults to `false` if not provided)
- Use `--sass` flag value (defaults to Tailwind if not provided)
- Skip all prompts and use provided options or sensible defaults

## Generated Structure

After running this command, your module will have:

```
Modules/
  Blog/
    app/
      Filament/
        Clusters/          # If clusters enabled
          Blog/
            Pages/
            Resources/
            Widgets/
        Pages/
        Resources/
        Widgets/
        BlogPlugin.php     # If plugins mode enabled
      Providers/
        Filament/
          BlogPanelProvider.php  # If panels mode enabled
        BlogServiceProvider.php
    resources/
      css/
        app.css           # Tailwind or app.scss (Sass)
      views/
    .gitignore
    README.md
    CHANGELOG.md
    package.json
    vite.config.js
    tailwind.config.js    # If Tailwind selected
```

## Integration Report

After installation, the command displays a comprehensive report including:

- Files created
- Panels created
- Plugins created
- Route information
- Assets information
- Next steps

## See Also

- [Getting Started Guide](../guides/getting-started.md)
- [Configuration](../configuration.md)

