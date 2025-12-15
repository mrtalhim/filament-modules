<?php

namespace Coolsam\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ModuleMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:migrate
                            {module : The name of the target module}
                            {--source= : Path to the external project source}
                            {--interactive : Run in interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate external Filament project into a module with interactive wizard';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $moduleName = $this->argument('module');
        $sourcePath = $this->option('source');
        $interactive = $this->option('interactive') || $this->input->isInteractive();

        $module = Module::find($moduleName);
        if (! $module) {
            $this->error("Module '{$moduleName}' not found.");
            return;
        }

        if (! $sourcePath) {
            if (! $interactive) {
                $this->error("Source path is required when not in interactive mode. Use --source option.");
                return;
            }
            $sourcePath = text(
                label: 'Enter the path to the external Filament project',
                placeholder: '/path/to/external/project',
                required: true
            );
        }

        if (! is_dir($sourcePath)) {
            $this->error("Source path does not exist: {$sourcePath}");
            return;
        }

        $this->info("🚀 Starting migration wizard for module: {$moduleName}");
        $this->info("📁 Source: {$sourcePath}");

        // Step 1: Validate source project
        $this->validateSourceProject($sourcePath);

        // Step 2: Plan the migration
        $migrationPlan = $this->planMigration($module, $sourcePath, $interactive);

        // Step 3: Execute migration
        $this->executeMigration($module, $sourcePath, $migrationPlan);

        // Step 4: Post-migration tasks
        $this->postMigrationTasks($module, $migrationPlan);

        $this->info("✅ Migration completed successfully!");
        $this->info("🎉 Module '{$moduleName}' is now ready for use.");
    }

    /**
     * Validate the source project.
     */
    protected function validateSourceProject(string $sourcePath): void
    {
        $this->info("🔍 Validating source project...");

        // Run the validation command
        $this->call('module:validate', [
            'path' => $sourcePath,
        ]);
    }

    /**
     * Plan the migration interactively.
     */
    protected function planMigration(\Nwidart\Modules\Module $module, string $sourcePath, bool $interactive): array
    {
        $this->info("📋 Planning migration...");

        $plan = [
            'namespace_update' => [],
            'route_migration' => [],
            'asset_handling' => [],
            'shared_models' => [],
        ];

        if ($interactive) {
            // Namespace updates
            $this->info("🏷️  Namespace Configuration:");
            if (confirm('Do you want to update namespaces in the migrated code?', true)) {
                $oldNamespace = text(
                    label: 'Current namespace in external project',
                    placeholder: 'App\\',
                    default: 'App\\'
                );

                $newNamespace = text(
                    label: 'New namespace for the module',
                    placeholder: "Modules\\{$module->getStudlyName()}\\",
                    default: "Modules\\{$module->getStudlyName()}\\"
                );

                $plan['namespace_update'] = [
                    'from' => $oldNamespace,
                    'to' => $newNamespace,
                ];
            }

            // Route migration
            $this->info("🛣️  Route Migration:");
            if (confirm('Do you want to migrate route names?', true)) {
                $routePrefix = text(
                    label: 'Route prefix for this module',
                    placeholder: $module->getKebabName(),
                    default: $module->getKebabName()
                );

                $plan['route_migration'] = [
                    'prefix' => $routePrefix,
                    'patterns' => [
                        'filament.app' => "filament.{$routePrefix}.app",
                        'filament.admin' => "filament.{$routePrefix}.admin",
                    ],
                ];
            }

            // Asset handling
            $this->info("🎨 Asset Handling:");
            $assetAction = select(
                label: 'How should assets be handled?',
                options: [
                    'copy' => 'Copy assets to module directory',
                    'link' => 'Create symlinks to original assets',
                    'vite' => 'Update Vite configuration to include assets',
                    'ignore' => 'Ignore assets for now',
                ],
                default: 'copy'
            );

            $plan['asset_handling'] = [
                'action' => $assetAction,
            ];

            // Shared models
            $this->info("📊 Shared Models:");
            if (confirm('Do you want to identify and handle shared models?', true)) {
                $plan['shared_models'] = [
                    'identify' => true,
                    'use_main_project' => confirm('Use main project models for shared entities (User, Role, etc.)?', true),
                ];
            }
        } else {
            // Non-interactive defaults
            $plan['namespace_update'] = [
                'from' => 'App\\',
                'to' => "Modules\\{$module->getStudlyName()}\\",
            ];
            $plan['route_migration'] = [
                'prefix' => $module->getKebabName(),
                'patterns' => [
                    'filament.app' => "filament.{$module->getKebabName()}.app",
                    'filament.admin' => "filament.{$module->getKebabName()}.admin",
                ],
            ];
            $plan['asset_handling'] = ['action' => 'copy'];
            $plan['shared_models'] = ['identify' => true, 'use_main_project' => true];
        }

        return $plan;
    }

    /**
     * Execute the migration.
     */
    protected function executeMigration(\Nwidart\Modules\Module $module, string $sourcePath, array $plan): void
    {
        $this->info("⚙️  Executing migration...");

        $filesystem = new Filesystem();
        $modulePath = $module->getPath();

        // Copy Filament files
        $this->copyFilamentFiles($filesystem, $sourcePath, $modulePath);

        // Apply namespace updates
        if (! empty($plan['namespace_update'])) {
            $this->applyNamespaceUpdates($module, $plan['namespace_update']);
        }

        // Apply route migration
        if (! empty($plan['route_migration'])) {
            $this->applyRouteMigration($module, $plan['route_migration']);
        }

        // Handle assets
        $this->handleAssets($module, $sourcePath, $plan['asset_handling']);

        // Handle shared models
        if (! empty($plan['shared_models'])) {
            $this->handleSharedModels($module, $plan['shared_models']);
        }
    }

    /**
     * Copy Filament-related files from source to module.
     */
    protected function copyFilamentFiles(Filesystem $filesystem, string $sourcePath, string $modulePath): void
    {
        $this->info("📂 Copying Filament files...");

        $copyMappings = [
            $sourcePath . '/app/Filament' => $modulePath . '/app/Filament',
            $sourcePath . '/app/Providers/Filament' => $modulePath . '/app/Providers/Filament',
            $sourcePath . '/resources/views/filament' => $modulePath . '/resources/views/filament',
            $sourcePath . '/resources/css' => $modulePath . '/resources/css',
            $sourcePath . '/resources/js' => $modulePath . '/resources/js',
        ];

        foreach ($copyMappings as $source => $destination) {
            if (is_dir($source)) {
                $filesystem->copyDirectory($source, $destination);
                $this->line("   Copied: " . basename($source));
            }
        }
    }

    /**
     * Apply namespace updates.
     */
    protected function applyNamespaceUpdates(\Nwidart\Modules\Module $module, array $config): void
    {
        $this->info("🏷️  Updating namespaces...");

        $this->call('module:namespace:update', [
            'module' => $module->getName(),
            '--from' => $config['from'],
            '--to' => $config['to'],
        ]);
    }

    /**
     * Apply route migration.
     */
    protected function applyRouteMigration(\Nwidart\Modules\Module $module, array $config): void
    {
        $this->info("🛣️  Migrating routes...");

        // This would require a more sophisticated route migration tool
        // For now, we'll note it as a manual step
        $this->warn("⚠️  Route migration requires manual review. Please check route names in migrated files.");
        $this->line("   Suggested patterns:");
        foreach ($config['patterns'] as $from => $to) {
            $this->line("   {$from} → {$to}");
        }
    }

    /**
     * Handle asset migration.
     */
    protected function handleAssets(\Nwidart\Modules\Module $module, string $sourcePath, array $config): void
    {
        $this->info("🎨 Handling assets...");

        switch ($config['action']) {
            case 'copy':
                // Assets are already copied in copyFilamentFiles
                $this->line("   Assets copied to module directory");
                break;
            case 'vite':
                $this->call('module:assets:discover', [
                    '--update-vite' => true,
                ]);
                break;
            case 'link':
                $this->warn("⚠️  Symlink creation requires manual setup");
                break;
            case 'ignore':
                $this->line("   Assets ignored as requested");
                break;
        }
    }

    /**
     * Handle shared models.
     */
    protected function handleSharedModels(\Nwidart\Modules\Module $module, array $config): void
    {
        $this->info("📊 Handling shared models...");

        if ($config['identify']) {
            $this->info("   Shared model detection would be implemented here");
            if ($config['use_main_project']) {
                $this->warn("⚠️  Please manually update model imports to use main project models");
            }
        }
    }

    /**
     * Perform post-migration tasks.
     */
    protected function postMigrationTasks(\Nwidart\Modules\Module $module, array $plan): void
    {
        $this->info("🧹 Running post-migration tasks...");

        // Generate route helpers if route migration was performed
        if (! empty($plan['route_migration'])) {
            if (confirm('Generate route helper trait for the migrated module?', true)) {
                $this->call('module:route:helper', [
                    'module' => $module->getName(),
                ]);
            }
        }

        // Run composer dump-autoload
        $this->info("📦 Running composer dump-autoload...");
        $process = Process::fromShellCommandline('composer dump-autoload');
        $process->run();

        if ($process->isSuccessful()) {
            $this->line("   ✅ Autoloader updated");
        } else {
            $this->warn("⚠️  Failed to update autoloader: " . $process->getErrorOutput());
        }

        // Final validation
        $this->info("🔍 Running final validation...");
        $this->call('module:health', [
            'module' => $module->getName(),
        ]);
    }
}
