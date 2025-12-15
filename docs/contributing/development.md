# Development Setup

Guide for setting up a development environment for Filament Modules.

## Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm (for frontend)
- Git

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/your-org/filament-modules.git
cd filament-modules
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Setup Testing

```bash
composer test
```

## Project Structure

```
filament-modules/
  src/              # Source code
  tests/            # Test files
  docs/             # Documentation
  stubs/            # Code generation stubs
  config/           # Configuration files
```

## Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
php artisan test --filter=GeneratorsTest
```

## Code Style

The project uses Laravel Pint for code style:

```bash
composer pint
```

## Development Workflow

1. Create feature branch
2. Make changes
3. Write/update tests
4. Run tests
5. Check code style
6. Submit pull request

## Testing Modules

Use the workbench directory for testing:

```bash
# Create test module
php artisan module:make TestModule

# Test commands
php artisan module:filament:install TestModule
```

## See Also

- [Fork Notes](fork-notes.md)

