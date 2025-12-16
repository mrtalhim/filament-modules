<?php

namespace Coolsam\Modules\Concerns;

use Illuminate\Support\Arr;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Module as ModuleInstance;

use function Laravel\Prompts\search;
use function Laravel\Prompts\select;

trait HandlesNonInteractiveMode
{
    /**
     * Check if command should run in non-interactive mode.
     */
    protected function isNonInteractive(): bool
    {
        return ! $this->input->isInteractive()
            || $this->option('no-interaction')
            || getenv('CI') === 'true';
    }

    /**
     * Ensure module argument is provided and valid.
     */
    protected function ensureModuleArgument(): ModuleInstance
    {
        $moduleName = $this->argument('module');

        if (! $moduleName) {
            if ($this->isNonInteractive()) {
                $this->errorNonInteractive(
                    'Module argument is required in non-interactive mode.',
                    'php artisan ' . $this->getName() . ' <module> [arguments...]'
                );
            }
            return $this->promptForModule();
        }

        return $this->validateModuleExists($moduleName);
    }

    /**
     * Ensure panel option is set, using defaults in non-interactive mode.
     */
    protected function ensurePanelOption(): ?string
    {
        if ($panel = $this->option('panel')) {
            return $panel;
        }

        if ($this->isNonInteractive()) {
            return filament()->getDefaultPanel()?->getId();
        }

        return $this->promptForPanel();
    }

    /**
     * Select namespace from available options.
     */
    protected function selectNamespace(array $namespaces, ?string $provided = null): string
    {
        if ($provided && in_array($provided, $namespaces)) {
            return $provided;
        }

        if (count($namespaces) === 1) {
            return Arr::first($namespaces);
        }

        if ($this->isNonInteractive()) {
            return Arr::first($namespaces); // Default to first
        }

        return $this->promptForNamespace($namespaces);
    }

    /**
     * Validate module exists and return Module instance.
     */
    protected function validateModuleExists(string $moduleName): ModuleInstance
    {
        $module = Module::find($moduleName);

        if (! $module) {
            $available = Module::all()->pluck('name')->join(', ');
            $this->error("Module '{$moduleName}' not found.");
            if ($available) {
                $this->line("Available modules: {$available}");
            }
            exit(1);
        }

        return $module;
    }

    /**
     * Show consistent error message for non-interactive mode failures.
     */
    protected function errorNonInteractive(string $message, ?string $usage = null): void
    {
        $this->newLine();
        $this->error($message);
        if ($usage) {
            $this->line("Usage: {$usage}");
        }
        $this->line("Run with --help to see all available options.");
        exit(1);
    }

    /**
     * Determine if file should be overwritten.
     */
    protected function shouldOverwriteFile(string $path): bool
    {
        if ($this->option('force')) {
            return true;
        }
        if ($this->isNonInteractive()) {
            return false; // Don't overwrite unless --force
        }
        return $this->confirm("File exists. Overwrite?", false);
    }

    /**
     * Prompt for module selection (interactive only).
     */
    protected function promptForModule(): ModuleInstance
    {
        $module = select('Please select the module:', Module::allEnabled());
        if (! $module) {
            $this->error('No module selected. Aborting.');
            exit(1);
        }
        $this->input->setArgument('module', $module);
        return Module::find($module);
    }

    /**
     * Prompt for panel selection (interactive only).
     * Override this in commands that need custom panel selection logic.
     */
    protected function promptForPanel(): ?string
    {
        $defaultPanel = filament()->getDefaultPanel();
        $options = [$defaultPanel->getId() => $defaultPanel->getId()];

        $panel = select('Please select the panel:', $options, default: $defaultPanel->getId());
        if ($panel) {
            $this->input->setOption('panel', $panel);
        }
        return $panel;
    }

    /**
     * Prompt for namespace selection (interactive only).
     */
    protected function promptForNamespace(array $namespaces): string
    {
        $keyedNamespaces = array_combine($namespaces, $namespaces);

        $namespace = search(
            label: 'Which namespace would you like to use?',
            options: function (?string $search) use ($keyedNamespaces): array {
                if (blank($search)) {
                    return $keyedNamespaces;
                }

                $search = str($search)->trim()->replace(['\\', '/'], '');

                return array_filter(
                    $keyedNamespaces,
                    fn (string $namespace): bool => str($namespace)->replace(['\\', '/'], '')->contains($search, ignoreCase: true)
                );
            },
        );

        return $namespace;
    }
}

