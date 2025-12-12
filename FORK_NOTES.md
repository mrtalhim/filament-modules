# Fork Plan: coolsam/modules

This document outlines changes to make in a fork of `coolsam/modules` to resolve the issues observed during testing.

## Goals
- Fix model namespace and model creation when generating resources.
- Ensure module Filament panels auto-register without manual bootstrap edits.
- Modernize frontend scaffolding for modules (Tailwind v4 + Vite alignment).
- Add regression tests for generators.

## Changes to Implement

### 1) Resource generation (model namespace & creation)
- File: `src/Commands/ModuleMakeFilamentResourceCommand.php`
- Adjust `ensureModelNamespace()`:
  - Default `model-namespace` to `Modules\<Module>\Models` when not provided.
  - If `--model` is used and the class doesn’t exist, create the model in `Modules/<Module>/app/Models/<Model>.php` (reuse Laravel/Filament model generation logic). Point the resource `use` to that FQN.
  - If the user supplies an FQN model argument, respect it (don’t strip namespaces).
- Ensure `resource-namespace` defaults to the module’s Filament namespace when panels/clusters aren’t chosen.
- Add tests covering:
  - `--model` without namespace creates model + correct `use`.
  - FQN model argument is preserved.

### 2) Panel auto-registration
- File: `src/ModulesServiceProvider.php`
- In auto-discovery, also scan and register `Modules/*/app/Providers/Filament/*PanelProvider.php` before Filament resolves.
- Ensure the glob matches actual paths and register each provider via `$this->app->register(...)`.
- Add an opt-out config flag if desired, defaulting to enabled.
- Tests:
  - Generated panel provider is auto-registered (routes exist) without manual `bootstrap/providers.php` edits.

### 3) Tailwind v4 + Vite alignment for modules
- Update module scaffolding stubs:
  - Module `package.json`: add `tailwindcss` ^4 and `@tailwindcss/vite`, bump `laravel-vite-plugin` to match current Filament/Laravel baseline (>= ^2.x), and align Vite version accordingly.
  - Module `vite.config.js`: include Tailwind plugin (`@tailwindcss/vite`) and mirror app defaults.
  - Optionally generate `tailwind.config.js` pointing to module resources.
- Remove/optionalize SASS-only default; Tailwind should be default with a flag to choose SASS.
- Tests:
  - Scaffolding emits Tailwind deps/config and a modern Vite config.

### 4) CLI ergonomics
- Add `--model-fqn` (or similar) to force FQN passthrough.
- Improve prompts to display chosen namespaces before writing.
- Add `--no-auto-register-panel` if you add panel auto-registration behavior and want opt-out.

### 5) Tests
- Add Pest/PHPUnit coverage for:
  - Resource gen with and without `--model-namespace` / FQN.
  - Panel gen produces routes/nav without manual provider registration.
  - Tailwind/Vite scaffolding emits expected files/deps.

## Nice-to-haves
- Better error messages when model namespace resolution fails.
- Option during module scaffolding to pick Tailwind vs SASS.
- Docs updates: how to override generators and how to disable/enable panel auto-registration.

## Status (completed in fork)
- Resource generation: fixes implemented. `--model` creates the model in the module by default; FQN input preserved via `--model-fqn`; resource namespace defaults to module Filament namespace. Tests added.
- Panel auto-registration: module panel providers are discovered (`Modules/*/app/Providers/Filament/*PanelProvider.php`) and registered automatically, with opt-out `auto_register_panels` config and `--no-auto-register-panel`. Immediate registration happens during panel generation. Tests added.
- Tailwind v4 + Vite: module stubs updated (Tailwind v4, `@tailwindcss/vite`, `laravel-vite-plugin` ^2) with default Tailwind preset and optional `--sass`. Tailwind/Vite stubs copy correctly. Tests added.
- CLI ergonomics: `--model-fqn` supported; clearer namespace logging; panel auto-register opt-out flag added.
- Tests: Pest feature and unit coverage added for resource generation, panel auto-registration, and Tailwind/Vite scaffolding. All tests passing via `composer test`.

## Observations & decisions
- Frontend build scope: scaffolding remains module-local (per-module `package.json` / Vite). This matches prior plugin behavior; you still run `npm install && npm run build` per module unless you centralize builds (e.g., workspaces or root Vite inputs).
- Panel discovery runs both before/after Filament resolves to catch providers in all boot orders; provider generation now requires the class file and registers the instance immediately when auto-register is enabled.
- Filament v4 resource generators output pluralized resource directories; tests assert those paths.

## References
- Filament generator anatomy: https://filamentphp.com/docs/4.x/advanced/file-generation#the-anatomy-of-a-class-generator