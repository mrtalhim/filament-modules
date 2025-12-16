<?php

namespace Coolsam\Modules\Commands;

use Coolsam\Modules\Concerns\HandlesNonInteractiveMode;
use Coolsam\Modules\Enums\ConfigMode;
use Coolsam\Modules\Facades\FilamentModules;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Console\Concerns\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Exceptions\ModuleNotFoundException;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\confirm;

class ModuleFilamentInstallCommand extends Command implements \Illuminate\Contracts\Console\PromptsForMissingInput
{
    use CanManipulateFiles;
    use HandlesNonInteractiveMode;
    use PromptsForMissingInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'module:filament:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Filament Support to a Module';

    private bool $cluster;

    private ConfigMode $mode = ConfigMode::BOTH;

    private string $moduleName;

    private string $frontendPreset = 'tailwind';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->moduleName = $this->argument('module');
        $this->mode = ConfigMode::tryFrom(\Config::get('filament-modules.mode', ConfigMode::BOTH->value));
        $interactive = ! $this->isNonInteractive() && ! app()->runningUnitTests();
        $this->cluster = $this->option('cluster')
            ? true
            : ($interactive ? confirm('Do you want to organize your code into filament clusters?', true) : false);
        $this->frontendPreset = $this->option('sass') ? 'sass' : 'tailwind';

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run3',
                'hypothesisId' => 'H3',
                'location' => 'ModuleFilamentInstallCommand::handle',
                'message' => 'handle start',
                'data' => [
                    'module' => $this->moduleName,
                    'frontendPreset' => $this->frontendPreset,
                    'modulesPath' => config('modules.paths.modules'),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
        }
        // #endregion agent log

        // Ensure the Filament directories exist
        $this->ensureFilamentDirectoriesExist();
        $this->ensureFrontendScaffolding();

        if ($this->mode->shouldRegisterPlugins()) {
            // Create Filament Plugin
            $this->createDefaultFilamentPlugin();
        }
        $shouldCreateDefaultCluster = $this->cluster && (
            $this->option('create-default-cluster')
            ? true
            : ($interactive ? confirm('Would you like to create a default Cluster for the module?', true) : false)
        );
        if ($shouldCreateDefaultCluster) {
            $this->createDefaultFilamentCluster();
        }

        if (config('filament-modules.module_panel.auto_create_on_install', true)) {
            // Create default Filament Panel
            $this->createDefaultFilamentPanel();
        }

        // Register module views in service provider
        $this->registerModuleViews();

        // Generate integration report
        $this->generateIntegrationReport();
    }

    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of the module in which to install filament support'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['cluster', 'C', InputOption::VALUE_NONE],
            ['sass', null, InputOption::VALUE_NONE],
            ['create-default-cluster', null, InputOption::VALUE_NONE, 'Create a default cluster for the module'],
        ];
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'module' => [
                'What is the name of the module?',
                'e.g AccessControl, Blog, etc.',
            ],
        ];
    }

    protected function getModule(): \Nwidart\Modules\Module
    {
        try {
            return Module::findOrFail($this->moduleName);
        } catch (ModuleNotFoundException | \Throwable $exception) {
            if ($this->isNonInteractive()) {
                $this->error("Module '{$this->moduleName}' does not exist.");
                $this->line("Run 'php artisan module:make {$this->moduleName}' to create it first.");
                exit(1);
            }
            if (confirm("Module $this->moduleName does not exist. Would you like to generate it?", true)) {
                $args = ['name' => [$this->moduleName]];

                if (config('filament-modules.module_panel.skip_nwidart_defaults', true)) {
                    $args['--plain'] = true;
                }

                $this->call('module:make', $args);

                return $this->getModule();
            }
            $this->error($exception->getMessage());
            exit(1);
        }
    }

    private function ensureFilamentDirectoriesExist(): void
    {
        if (! is_dir($dir = $this->getModule()->appPath('Filament'))) {
            $this->makeDirectory($dir);
        }

        if (! is_dir($dir = $this->getModule()->appPath('Providers' . DIRECTORY_SEPARATOR . 'Filament'))) {
            $this->makeDirectory($dir);
        }

        if ($this->cluster) {
            $dir = $this->getModule()->appPath('Filament/Clusters');
            if (! is_dir($dir = $this->getModule()->appPath('Filament/Clusters'))) {
                $this->makeDirectory($dir);
            }

        } else {
            if (! is_dir($dir = $this->getModule()->appPath('Filament/Pages'))) {
                $this->makeDirectory($dir);
            }

            if (! is_dir($dir = $this->getModule()->appPath('Filament/Resources'))) {
                $this->makeDirectory($dir);
            }

            if (! is_dir($dir = $this->getModule()->appPath('Filament/Widgets'))) {
                $this->makeDirectory($dir);
            }
        }
    }

    private function ensureFrontendScaffolding(): void
    {
        $module = $this->getModule();
        $filesystem = app(Filesystem::class);

        $stubBase = FilamentModules::packagePath('stubs/module/' . $this->frontendPreset);

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run3',
                'hypothesisId' => 'H3',
                'location' => 'ModuleFilamentInstallCommand::ensureFrontendScaffolding',
                'message' => 'stub base check',
                'data' => [
                    'stubBase' => $stubBase,
                    'exists' => is_dir($stubBase),
                    'modulePath' => $module->getPath(),
                    'preset' => $this->frontendPreset,
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
        }
        // #endregion agent log

        if (! is_dir($stubBase)) {
            return;
        }

        $stubs = [
            $stubBase . DIRECTORY_SEPARATOR . 'package.json' => $module->getPath() . DIRECTORY_SEPARATOR . 'package.json',
            $stubBase . DIRECTORY_SEPARATOR . 'vite.config.js' => $module->getExtraPath('vite.config.js'),
            FilamentModules::packagePath('stubs/module/.gitignore.stub') => $module->getPath() . DIRECTORY_SEPARATOR . '.gitignore',
            FilamentModules::packagePath('stubs/module/README.md.stub') => $module->getPath() . DIRECTORY_SEPARATOR . 'README.md',
            FilamentModules::packagePath('stubs/module/CHANGELOG.md.stub') => $module->getPath() . DIRECTORY_SEPARATOR . 'CHANGELOG.md',
        ];

        if ($this->frontendPreset === 'tailwind') {
            $stubs[$stubBase . DIRECTORY_SEPARATOR . 'tailwind.config.js'] = $module->getExtraPath('tailwind.config.js');
            $stubs[$stubBase . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'app.css'] = $module->resourcesPath('css/app.css');
        } else {
            $stubs[$stubBase . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'app.scss'] = $module->resourcesPath('css/app.scss');
        }

        foreach ($stubs as $from => $to) {
            if (! file_exists($from)) {
                continue;
            }

            $filesystem->ensureDirectoryExists(pathinfo($to, PATHINFO_DIRNAME));

            // Handle template files with replacements
            if (str_ends_with($from, 'README.md.stub') || str_ends_with($from, 'CHANGELOG.md.stub')) {
                $content = file_get_contents($from);
                $content = $this->applyTemplateReplacements($content, $module);
                $filesystem->put($to, $content);
            } else {
                $filesystem->copy($from, $to);
            }

            $this->info("Scaffolded: {$to}");

            // #region agent log
            try {
                $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
                app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
                $payload = json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run3',
                    'hypothesisId' => 'H3',
                    'location' => 'ModuleFilamentInstallCommand::ensureFrontendScaffolding',
                    'message' => 'scaffold copy',
                    'data' => [
                        'from' => $from,
                        'to' => $to,
                        'exists_after' => file_exists($to),
                        'preset' => $this->frontendPreset,
                    ],
                    'timestamp' => round(microtime(true) * 1000),
                ]);
                file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
            } catch (\Throwable $e) {
                // swallow
            }
            // #endregion agent log
        }

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run3',
                'hypothesisId' => 'H3',
                'location' => 'ModuleFilamentInstallCommand::ensureFrontendScaffolding',
                'message' => 'scaffold loop end',
                'data' => [
                    'total_targets' => count($stubs),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
        }
        // #endregion agent log
    }

    private function makeDirectory(string $dir): void
    {
        if (! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error(sprintf('Directory "%s" was not created', $dir));
            exit(1);
        }
    }

    protected function createDefaultFilamentPlugin(): void
    {
        $module = $this->getModule();
        $this->call('module:filament:plugin', [
            'name' => $module->getStudlyName() . 'Plugin',
            'module' => $module->getStudlyName(),
        ]);
    }

    protected function createDefaultFilamentCluster(): void
    {
        $module = $this->getModule();
        $this->call('module:filament:cluster', [
            'name' => $module->getStudlyName(),
            'module' => $module->getStudlyName(),
            '--panel' => filament()->getDefaultPanel()->getId(),
        ]);
    }

    protected function createDefaultFilamentPanel(): void
    {
        $module = $this->getModule();
        $this->call('module:make:filament-panel', [
            'id' => config('filament-modules.module_panel.default_id', 'admin'),
            'module' => $module->getStudlyName(),
        ]);
    }

    protected function registerModuleViews(): void
    {
        $module = $this->getModule();
        $serviceProviderPath = $module->appPath('Providers/' . $module->getStudlyName() . 'ServiceProvider.php');

        if (! file_exists($serviceProviderPath)) {
            $this->warn("Service provider not found at {$serviceProviderPath}. Skipping view registration.");
            return;
        }

        $content = file_get_contents($serviceProviderPath);

        // Check if view registration is already present
        if (str_contains($content, 'loadViewsFrom')) {
            $this->info('View registration already exists in service provider.');
            return;
        }

        // Generate view namespace from module name (kebab-case)
        $viewNamespace = str($module->getName())->kebab()->toString();

        // Add view registration to the boot method
        $viewRegistrationCode = <<<PHP

    public function boot()
    {
        \$this->loadViewsFrom(
            module_path('{$module->getName()}', 'resources/views'),
            '{$viewNamespace}'
        );
    }
PHP;

        // Replace empty boot method or add to existing boot method
        if (str_contains($content, 'public function boot(): void')) {
            // Replace empty boot method
            $content = str_replace(
                '    public function boot(): void {}',
                $viewRegistrationCode,
                $content
            );
        } elseif (str_contains($content, 'public function boot()')) {
            // Replace empty boot method (without return type)
            $content = str_replace(
                '    public function boot() {}',
                $viewRegistrationCode,
                $content
            );
        } else {
            // Add boot method if it doesn't exist
            $content = str_replace(
                '    public function register(): void {}',
                '    public function register(): void {}

' . $viewRegistrationCode,
                $content
            );
        }

        file_put_contents($serviceProviderPath, $content);
        $this->info("Registered module views with namespace '{$viewNamespace}' in service provider.");
    }

    protected function applyTemplateReplacements(string $content, \Nwidart\Modules\Module $module): string
    {
        $panelId = config('filament-modules.module_panel.default_id', 'admin');
        $moduleKebabName = $module->getKebabName();
        $panelIdWithModule = str($moduleKebabName)->append('-')->append($panelId)->toString();

        // Determine URL path based on configuration strategy
        $pathStrategy = config('filament-modules.module_panel.path_strategy', 'module_only');
        switch ($pathStrategy) {
            case 'module_prefix_with_id':
                $panelPath = str($moduleKebabName)->append('/')->append($panelId)->toString();
                break;
            case 'panel_id_only':
                $panelPath = $panelId;
                break;
            case 'module_only':
            default:
                $panelPath = $moduleKebabName;
                break;
        }

        $replacements = [
            '{{ module_name }}' => $module->getTitle() ?: $module->getStudlyName(),
            '{{ module_description }}' => 'A Filament module for ' . ($module->getTitle() ?: $module->getStudlyName()),
            '{{ panel_name }}' => str($panelId)->studly()->snake()->title()->replace(['_', '-'], ' ')->toString(),
            '{{ panel_path }}' => $panelPath,
            '{{ laravel_version }}' => app()->version(),
            '{{ filament_version }}' => \Composer\InstalledVersions::getVersion('filament/filament') ?? '3.x',
            '{{ php_version }}' => PHP_VERSION,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    protected function generateIntegrationReport(): void
    {
        $module = $this->getModule();

        $this->info("📋 Integration Report for Module: {$module->getName()}");
        $this->line("═══════════════════════════════════════════════════════════════");

        // Files Created
        $this->reportSection("📁 Files Created", $this->getCreatedFiles($module));

        // Panels Created
        $this->reportSection("📊 Panels Created", $this->getCreatedPanels($module));

        // Plugins Created
        if ($this->mode->shouldRegisterPlugins()) {
            $this->reportSection("🔌 Plugins Created", $this->getCreatedPlugins($module));
        }

        // Route Information
        $this->reportSection("🛣️  Route Information", $this->getRouteInformation($module));

        // Assets Information
        $this->reportSection("🎨 Assets Information", $this->getAssetsInformation($module));

        // Next Steps
        $this->reportSection("🚀 Next Steps", $this->getNextSteps($module));

        $this->line("═══════════════════════════════════════════════════════════════");
        $this->info("✅ Module installation completed successfully!");
    }

    protected function reportSection(string $title, array $items): void
    {
        $this->line("{$title}:");

        if (empty($items)) {
            $this->line("   (none)");
        } else {
            foreach ($items as $item) {
                $this->line("   • {$item}");
            }
        }

        $this->line("");
    }

    protected function getCreatedFiles(\Nwidart\Modules\Module $module): array
    {
        $files = [];

        // Check for service provider
        $serviceProviderPath = $module->appPath('Providers/' . $module->getStudlyName() . 'ServiceProvider.php');
        if (file_exists($serviceProviderPath)) {
            $files[] = "Service Provider: app/Providers/{$module->getStudlyName()}ServiceProvider.php";
        }

        // Check for Filament directories
        $filamentDirectories = [
            'app/Filament',
            'app/Filament/Pages',
            'app/Filament/Resources',
            'app/Filament/Widgets',
            'app/Providers/Filament',
        ];

        foreach ($filamentDirectories as $dir) {
            if (is_dir($module->appPath($dir))) {
                $files[] = "Directory: {$dir}/";
            }
        }

        // Check for frontend files
        $frontendFiles = [
            'package.json',
            'vite.config.js',
            'tailwind.config.js',
            'resources/css/app.css',
            'resources/js/app.js',
            '.gitignore',
            'README.md',
            'CHANGELOG.md',
        ];

        foreach ($frontendFiles as $file) {
            $filePath = $module->getPath() . '/' . $file;
            if (file_exists($filePath)) {
                $files[] = "File: {$file}";
            }
        }

        return $files;
    }

    protected function getCreatedPanels(\Nwidart\Modules\Module $module): array
    {
        $panels = [];

        // Check for panel providers
        $panelProviderPath = $module->appPath('Providers/Filament');
        if (is_dir($panelProviderPath)) {
            $panelFiles = glob($panelProviderPath . '/*PanelProvider.php');
            foreach ($panelFiles as $panelFile) {
                $panelName = basename($panelFile, 'PanelProvider.php');
                $panels[] = "{$panelName} Panel Provider";
            }
        }

        return $panels;
    }

    protected function getCreatedPlugins(\Nwidart\Modules\Module $module): array
    {
        $plugins = [];

        // Check for plugin files
        $pluginPath = $module->appPath('Filament');
        if (is_dir($pluginPath)) {
            $pluginFiles = glob($pluginPath . '/*Plugin.php');
            foreach ($pluginFiles as $pluginFile) {
                $pluginName = basename($pluginFile, 'Plugin.php');
                $plugins[] = "{$pluginName} Plugin";
            }
        }

        return $plugins;
    }

    protected function getRouteInformation(\Nwidart\Modules\Module $module): array
    {
        $info = [];

        // Get panels to determine route prefixes
        $panels = FilamentModules::getModulePanels($module->getName());
        foreach ($panels as $panel) {
            $panelId = $panel->getId();
            $routePrefix = config('filament-modules.module_panel.route_prefix_pattern', '{panel-id}');
            $actualPrefix = str_replace(
                ['{panel-id}', '{module-slug}'],
                [$panelId, $module->getKebabName()],
                $routePrefix
            );

            $info[] = "Panel '{$panelId}' uses route prefix: '{$actualPrefix}'";
        }

        if (empty($info)) {
            $info[] = "No panels configured yet. Run 'php artisan module:make:filament-panel' to create panels.";
        }

        return $info;
    }

    protected function getAssetsInformation(\Nwidart\Modules\Module $module): array
    {
        $info = [];

        // Check for CSS files
        $cssFiles = glob($module->getPath() . '/resources/css/**/*.css');
        if (! empty($cssFiles)) {
            $info[] = count($cssFiles) . " CSS file(s) found in resources/css/";
        }

        // Check for JS files
        $jsFiles = glob($module->getPath() . '/resources/js/**/*.js');
        if (! empty($jsFiles)) {
            $info[] = count($jsFiles) . " JavaScript file(s) found in resources/js/";
        }

        if (! empty($cssFiles) || ! empty($jsFiles)) {
            $info[] = "Run 'php artisan module:assets:discover --update-vite' to register assets";
        } else {
            $info[] = "No asset files found";
        }

        return $info;
    }

    protected function getNextSteps(\Nwidart\Modules\Module $module): array
    {
        $steps = [
            "Create resources: php artisan module:make:filament-resource MyResource {$module->getName()}",
            "Create pages: php artisan module:make:filament-page MyPage {$module->getName()}",
            "Create panels: php artisan module:make:filament-panel MyPanel {$module->getName()}",
            "Check module health: php artisan module:health {$module->getName()}",
        ];

        // Add asset discovery if assets exist
        $hasAssets = ! empty(glob($module->getPath() . '/resources/{css,js}/**/*.{css,js}', GLOB_BRACE));
        if ($hasAssets) {
            array_splice($steps, 3, 0, ["Discover assets: php artisan module:assets:discover"]);
        }

        return $steps;
    }
}
