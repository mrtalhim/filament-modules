<?php

namespace Coolsam\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Finder\Finder;

class ModuleValidateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:validate
                            {path : Path to the external project to validate}
                            {--target-module= : Target module name for integration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate external project compatibility before module integration';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $externalPath = $this->argument('path');
        $targetModule = $this->option('target-module');

        if (! is_dir($externalPath)) {
            $this->error("External project path does not exist: {$externalPath}");
            return;
        }

        $this->info("🔍 Validating external project: {$externalPath}");

        $issues = [];
        $warnings = [];

        // Check if target module exists
        if ($targetModule) {
            $module = Module::find($targetModule);
            if (! $module) {
                $this->error("Target module '{$targetModule}' does not exist.");
                return;
            }
            $this->info("🎯 Target module: {$targetModule}");
        }

        // 1. Check Filament version compatibility
        $this->checkFilamentVersionCompatibility($externalPath, $issues, $warnings);

        // 2. Check for namespace conflicts
        $this->checkNamespaceConflicts($externalPath, $issues);

        // 3. Check route name patterns
        $this->checkRouteNamePatterns($externalPath, $warnings);

        // 4. Check for required dependencies
        $this->checkRequiredDependencies($externalPath, $issues);

        // 5. Check for shared models
        $this->checkSharedModels($externalPath, $warnings);

        // Report results
        $this->reportResults($issues, $warnings);

        if (! empty($issues)) {
            $this->error("❌ Validation failed! Please fix the issues above before integration.");
            return;
        }

        $this->info("✅ Validation passed! The external project appears compatible.");
        if (! empty($warnings)) {
            $this->warn("⚠️  Review the warnings above for potential issues.");
        }
    }

    /**
     * Check Filament version compatibility.
     */
    protected function checkFilamentVersionCompatibility(string $externalPath, array &$issues, array &$warnings): void
    {
        $this->info("📦 Checking Filament version compatibility...");

        $composerPath = $externalPath . '/composer.json';
        if (! file_exists($composerPath)) {
            $issues[] = "No composer.json found in external project";
            return;
        }

        $composerData = json_decode(file_get_contents($composerPath), true);
        $externalFilamentVersion = $composerData['require']['filament/filament'] ?? null;

        if (! $externalFilamentVersion) {
            $issues[] = "Filament dependency not found in composer.json";
            return;
        }

        // Get current project's Filament version
        $currentComposerPath = base_path('composer.json');
        if (file_exists($currentComposerPath)) {
            $currentComposerData = json_decode(file_get_contents($currentComposerPath), true);
            $currentFilamentVersion = $currentComposerData['require']['filament/filament'] ?? null;

            if ($currentFilamentVersion && $externalFilamentVersion !== $currentFilamentVersion) {
                $warnings[] = "Filament version mismatch: external project uses {$externalFilamentVersion}, current project uses {$currentFilamentVersion}";
            }
        }

        $this->line("   External project Filament version: {$externalFilamentVersion}");
    }

    /**
     * Check for namespace conflicts.
     */
    protected function checkNamespaceConflicts(string $externalPath, array &$issues): void
    {
        $this->info("🏷️  Checking for namespace conflicts...");

        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->in($externalPath . '/app')
            ->exclude(['vendor']);

        $namespaces = [];
        foreach ($finder as $file) {
            $content = $file->getContents();
            if (preg_match('/^namespace\s+([^;]+)/m', $content, $matches)) {
                $namespace = trim($matches[1]);
                if (isset($namespaces[$namespace])) {
                    $namespaces[$namespace][] = $file->getRelativePathname();
                } else {
                    $namespaces[$namespace] = [$file->getRelativePathname()];
                }
            }
        }

        // Check for conflicts with Modules namespace
        foreach ($namespaces as $namespace => $files) {
            if (str_starts_with($namespace, 'Modules\\')) {
                $issues[] = "Namespace conflict: {$namespace} already uses Modules namespace (files: " . implode(', ', $files) . ")";
            }
        }

        $this->line("   Found " . count($namespaces) . " unique namespaces");
    }

    /**
     * Check route name patterns.
     */
    protected function checkRouteNamePatterns(string $externalPath, array &$warnings): void
    {
        $this->info("🛣️  Checking route name patterns...");

        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->in($externalPath)
            ->exclude(['vendor'])
            ->contains('route\(');

        $genericRoutes = [];
        foreach ($finder as $file) {
            $content = $file->getContents();
            // Look for hardcoded filament routes that might conflict
            if (preg_match_all('/route\(["\']filament\.([^"\'\)]+)/', $content, $matches)) {
                foreach ($matches[1] as $routeName) {
                    if (! str_contains($routeName, 'app.') && ! str_contains($routeName, 'admin.')) {
                        $genericRoutes[] = $routeName;
                    }
                }
            }
        }

        if (! empty($genericRoutes)) {
            $warnings[] = "Found generic route names that may conflict: " . implode(', ', array_unique($genericRoutes));
        }

        $this->line("   Route pattern check completed");
    }

    /**
     * Check for required dependencies.
     */
    protected function checkRequiredDependencies(string $externalPath, array &$issues): void
    {
        $this->info("📋 Checking required dependencies...");

        $composerPath = $externalPath . '/composer.json';
        if (! file_exists($composerPath)) {
            $issues[] = "No composer.json found in external project";
            return;
        }

        $composerData = json_decode(file_get_contents($composerPath), true);
        $dependencies = $composerData['require'] ?? [];

        $required = [
            'filament/filament' => 'Filament framework',
            'laravel/framework' => 'Laravel framework',
        ];

        foreach ($required as $package => $description) {
            if (! isset($dependencies[$package])) {
                $issues[] = "Missing required dependency: {$package} ({$description})";
            }
        }

        $this->line("   Dependencies check completed");
    }

    /**
     * Check for shared models that should be identified.
     */
    protected function checkSharedModels(string $externalPath, array &$warnings): void
    {
        $this->info("📊 Checking for shared models...");

        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->in($externalPath . '/app/Models')
            ->exclude(['vendor']);

        $models = [];
        foreach ($finder as $file) {
            $content = $file->getContents();
            if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
                $models[] = $matches[1];
            }
        }

        // Common shared models that might need special handling
        $sharedModelPatterns = ['User', 'Role', 'Permission', 'Setting'];
        $potentialShared = array_intersect($models, $sharedModelPatterns);

        if (! empty($potentialShared)) {
            $warnings[] = "Potential shared models detected: " . implode(', ', $potentialShared) . ". Consider using main project models instead.";
        }

        $this->line("   Found " . count($models) . " models");
    }

    /**
     * Report validation results.
     */
    protected function reportResults(array $issues, array $warnings): void
    {
        if (! empty($issues)) {
            $this->error("🚨 Issues found:");
            foreach ($issues as $issue) {
                $this->line("   ❌ {$issue}");
            }
        }

        if (! empty($warnings)) {
            $this->warn("⚠️  Warnings:");
            foreach ($warnings as $warning) {
                $this->line("   ⚠️  {$warning}");
            }
        }
    }
}
