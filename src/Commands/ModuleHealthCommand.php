<?php

namespace Coolsam\Modules\Commands;

use Coolsam\Modules\Facades\FilamentModules;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Finder\Finder;

class ModuleHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:health
                            {module : The name of the module to check}
                            {--fix : Attempt to automatically fix issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health of a module and identify potential issues';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $moduleName = $this->argument('module');
        $autoFix = $this->option('fix');

        $module = Module::find($moduleName);
        if (! $module) {
            $this->error("Module '{$moduleName}' not found.");
            return;
        }

        $this->info("🏥 Checking health of module: {$moduleName}");

        $issues = [];
        $warnings = [];
        $checks = 0;

        // Run all health checks
        $checks += $this->checkNamespaceConsistency($module, $issues);
        $checks += $this->checkRouteValidity($module, $issues, $warnings);
        $checks += $this->checkViewRegistration($module, $issues);
        $checks += $this->checkAssetConfiguration($module, $warnings);
        $checks += $this->checkServiceProviderRegistration($module, $issues);
        $checks += $this->checkPanelConfiguration($module, $issues, $warnings);
        $checks += $this->checkFileStructure($module, $warnings);

        // Report results
        $this->reportHealthResults($checks, $issues, $warnings);

        // Auto-fix if requested
        if ($autoFix && (! empty($issues) || ! empty($warnings))) {
            $this->attemptAutoFix($module, $issues, $warnings);
        }
    }

    /**
     * Check namespace consistency.
     */
    protected function checkNamespaceConsistency(\Nwidart\Modules\Module $module, array &$issues): int
    {
        $this->info("🏷️  Checking namespace consistency...");

        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->in($module->getPath())
            ->exclude(['vendor']);

        $inconsistentNamespaces = 0;
        $expectedNamespace = $module->appNamespace('');

        foreach ($finder as $file) {
            $content = $file->getContents();
            $relativePath = str_replace($module->getPath() . '/', '', $file->getPathname());

            // Check namespace declaration
            if (preg_match('/^namespace\s+([^;]+)/m', $content, $matches)) {
                $declaredNamespace = $matches[1];
                if (! str_starts_with($declaredNamespace, rtrim($expectedNamespace, '\\'))) {
                    $issues[] = "Inconsistent namespace in {$relativePath}: expected to start with '{$expectedNamespace}', found '{$declaredNamespace}'";
                    $inconsistentNamespaces++;
                }
            }

            // Check class name matches file name
            if (preg_match('/^class\s+(\w+)/m', $content, $matches)) {
                $className = $matches[1];
                $expectedClassName = basename($file->getFilename(), '.php');
                if ($className !== $expectedClassName) {
                    $issues[] = "Class name mismatch in {$relativePath}: expected '{$expectedClassName}', found '{$className}'";
                    $inconsistentNamespaces++;
                }
            }
        }

        $this->line("   Checked " . count(iterator_to_array($finder)) . " PHP files");
        return 1;
    }

    /**
     * Check route validity.
     */
    protected function checkRouteValidity(\Nwidart\Modules\Module $module, array &$issues, array &$warnings): int
    {
        $this->info("🛣️  Checking route validity...");

        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->in($module->getPath())
            ->contains('route(');

        $routeIssues = 0;
        foreach ($finder as $file) {
            $content = $file->getContents();
            $relativePath = str_replace($module->getPath() . '/', '', $file->getPathname());

            // Find all route() calls
            if (preg_match_all('/route\(["\']([^"\'\)]+)/', $content, $matches)) {
                foreach ($matches[1] as $routeName) {
                    // Check if route exists
                    try {
                        route($routeName);
                    } catch (\Exception $e) {
                        $issues[] = "Invalid route reference in {$relativePath}: '{$routeName}' does not exist";
                        $routeIssues++;
                    }
                }
            }

            // Check for hardcoded generic routes
            if (preg_match_all('/["\']filament\.app\.([^"\'\)]+)/', $content, $matches)) {
                foreach ($matches[1] as $routePart) {
                    if (! str_contains($routePart, $module->getKebabName())) {
                        $warnings[] = "Potential generic route in {$relativePath}: filament.app.{$routePart} may conflict with module routes";
                    }
                }
            }
        }

        $this->line("   Checked " . count(iterator_to_array($finder)) . " files with routes");
        return 1;
    }

    /**
     * Check view registration.
     */
    protected function checkViewRegistration(\Nwidart\Modules\Module $module, array &$issues): int
    {
        $this->info("👁️  Checking view registration...");

        $serviceProviderPath = $module->appPath('Providers/' . $module->getStudlyName() . 'ServiceProvider.php');

        if (! file_exists($serviceProviderPath)) {
            $issues[] = "Module service provider not found: {$serviceProviderPath}";
            return 1;
        }

        $content = file_get_contents($serviceProviderPath);
        $viewNamespace = str($module->getName())->kebab()->toString();

        // Check if loadViewsFrom is registered
        if (! str_contains($content, 'loadViewsFrom')) {
            $issues[] = "View namespace not registered in service provider. Run: php artisan module:filament:install {$module->getName()}";
        } elseif (! str_contains($content, $viewNamespace)) {
            $issues[] = "Incorrect view namespace in service provider. Expected: '{$viewNamespace}'";
        } else {
            $this->line("   ✅ View namespace '{$viewNamespace}' is properly registered");
        }

        // Check if views directory exists
        $viewsPath = $module->resourcesPath('views');
        if (! is_dir($viewsPath)) {
            $issues[] = "Views directory does not exist: {$viewsPath}";
        }

        return 1;
    }

    /**
     * Check asset configuration.
     */
    protected function checkAssetConfiguration(\Nwidart\Modules\Module $module, array &$warnings): int
    {
        $this->info("🎨 Checking asset configuration...");

        $assets = [
            'CSS' => glob($module->getPath() . '/resources/css/**/*.css'),
            'JS' => glob($module->getPath() . '/resources/js/**/*.js'),
        ];

        $totalAssets = count($assets['CSS']) + count($assets['JS']);

        if ($totalAssets > 0) {
            $warnings[] = "Module has {$totalAssets} asset files that may need to be registered in vite.config.js. Run: php artisan module:assets:discover --update-vite";
        } else {
            $this->line("   ✅ No asset files found (or assets are properly configured)");
        }

        return 1;
    }

    /**
     * Check service provider registration.
     */
    protected function checkServiceProviderRegistration(\Nwidart\Modules\Module $module, array &$issues): int
    {
        $this->info("📋 Checking service provider registration...");

        $serviceProviderClass = $module->appNamespace("Providers\\{$module->getStudlyName()}ServiceProvider");

        if (! class_exists($serviceProviderClass)) {
            $issues[] = "Module service provider class not found: {$serviceProviderClass}";
            return 1;
        }

        // Check if service provider is registered in composer.json or bootstrap/providers.php
        $registered = false;

        // Check composer.json autoload
        $composerPath = $module->getPath() . '/composer.json';
        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);
            $psr4 = $composer['autoload']['psr-4'] ?? [];
            foreach ($psr4 as $namespace => $path) {
                if (str_starts_with($serviceProviderClass, $namespace)) {
                    $registered = true;
                    break;
                }
            }
        }

        if (! $registered) {
            $warnings[] = "Service provider may not be auto-loaded. Check composer.json autoload configuration.";
        } else {
            $this->line("   ✅ Service provider is properly configured for autoloading");
        }

        return 1;
    }

    /**
     * Check panel configuration.
     */
    protected function checkPanelConfiguration(\Nwidart\Modules\Module $module, array &$issues, array &$warnings): int
    {
        $this->info("📊 Checking panel configuration...");

        $panels = FilamentModules::getModulePanels($module->getName());

        if (empty($panels)) {
            $issues[] = "No Filament panels found for module. Run: php artisan module:filament:install {$module->getName()}";
            return 1;
        }

        foreach ($panels as $panel) {
            $panelId = $panel->getId();
            $this->line("   Found panel: {$panelId}");

            // Check if panel ID follows expected pattern
            $expectedPattern = config('filament-modules.module_panel.panel_id_pattern', '{module-slug}-{panel-name}');
            $expectedId = str_replace(
                ['{module-slug}', '{panel-name}'],
                [$module->getKebabName(), 'admin'], // Default panel name
                $expectedPattern
            );

            if (! str_starts_with($panelId, $module->getKebabName())) {
                $warnings[] = "Panel ID '{$panelId}' does not follow expected naming pattern";
            }
        }

        return 1;
    }

    /**
     * Check file structure.
     */
    protected function checkFileStructure(\Nwidart\Modules\Module $module, array &$warnings): int
    {
        $this->info("📁 Checking file structure...");

        $requiredStructure = [
            'app/Filament' => 'Filament directory',
            'app/Providers' => 'Providers directory',
            'resources/views' => 'Views directory',
            '.gitignore' => 'Git ignore file',
            'README.md' => 'README file',
        ];

        foreach ($requiredStructure as $path => $description) {
            $fullPath = $module->getPath() . '/' . $path;
            if (! file_exists($fullPath) && ! is_dir($fullPath)) {
                $warnings[] = "Missing {$description}: {$path}";
            }
        }

        $this->line("   ✅ File structure check completed");
        return 1;
    }

    /**
     * Report health check results.
     */
    protected function reportHealthResults(int $checks, array $issues, array $warnings): void
    {
        $this->info("📊 Health Check Summary:");
        $this->line("   ✅ Checks performed: {$checks}");

        if (empty($issues) && empty($warnings)) {
            $this->info("🎉 Module is healthy! No issues found.");
            return;
        }

        if (! empty($issues)) {
            $this->error("🚨 Critical Issues (" . count($issues) . "):");
            foreach ($issues as $issue) {
                $this->line("   ❌ {$issue}");
            }
        }

        if (! empty($warnings)) {
            $this->warn("⚠️  Warnings (" . count($warnings) . "):");
            foreach ($warnings as $warning) {
                $this->line("   ⚠️  {$warning}");
            }
        }

        if (! empty($issues)) {
            $this->error("💥 Module has critical issues that should be addressed.");
        }
    }

    /**
     * Attempt to auto-fix issues.
     */
    protected function attemptAutoFix(\Nwidart\Modules\Module $module, array $issues, array $warnings): void
    {
        $this->info("🔧 Attempting auto-fix...");

        $fixed = 0;

        // Try to fix view registration issues
        if (in_array("View namespace not registered in service provider. Run: php artisan module:filament:install {$module->getName()}", $issues)) {
            $this->call('module:filament:install', [
                'module' => $module->getName(),
            ]);
            $fixed++;
        }

        if ($fixed > 0) {
            $this->info("✅ Auto-fixed {$fixed} issue(s). Run health check again to verify.");
        } else {
            $this->warn("⚠️  No auto-fixable issues found.");
        }
    }
}
