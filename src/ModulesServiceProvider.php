<?php

namespace Coolsam\Modules;

use Coolsam\Modules\Facades\FilamentModules;
use Coolsam\Modules\Testing\TestsModules;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Nwidart\Modules\Module;
use Nwidart\Modules\Facades\Module as ModuleFacade;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModulesServiceProvider extends PackageServiceProvider
{
    public static string $name = 'modules';

    public static string $viewNamespace = 'modules';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->endWith(function (InstallCommand $command) {
                        $command->askToStarRepoOnGitHub('savannabits/filament-modules');
                    });
            });

        $configFileName = 'filament-modules';

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile($configFileName);
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->registerModuleMacros();
        $this->autoDiscoverPanels();
    }

    public function attemptToRegisterModuleProviders(): void
    {
        // It is necessary to register them here to avoid late registration (after Panels have already been booted)
        $pattern1 = config(
            'modules.paths.modules',
            'Modules'
        ) . '/*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . '*Provider.php';
        $pattern2 = config(
            'modules.paths.modules',
            'Modules'
        ) . '/*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . 'Filament' . DIRECTORY_SEPARATOR . '*Provider.php';
        $serviceProviders = glob($pattern1);
        $panelProviders = glob($pattern2);
        $providers = array_merge($serviceProviders, $panelProviders);

        foreach ($providers as $provider) {
            $namespace = FilamentModules::convertPathToNamespace($provider);
            $module = str($namespace)->before('\Providers\\')->afterLast('\\')->toString();
            $className = str($namespace)->afterLast('\\')->toString();
            if (str($className)->startsWith($module)) {
                // Skip disabled modules (only check if module system is available)
                try {
                    $foundModule = ModuleFacade::find($module);
                    if ($foundModule && ! $foundModule->isEnabled()) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    // Module system not available yet, continue with registration
                }

                // register the module service provider
                if (! class_exists($namespace)) {
                    continue;
                }
                $this->app->register($namespace);
            }
        }
    }

    public function autoDiscoverPanels(): void
    {
        if (! config('filament-modules.auto_register_panels', true)) {
            return;
        }

        $registerPanels = function () {
            $panels = $this->discoverPanelProviders();

            foreach ($panels as $panel) {
                // Extract module name from panel class namespace
                $moduleName = str($panel)->after('Modules\\')->before('\\Providers\\Filament')->toString();

                // Skip disabled modules (only check if module system is available)
                try {
                    $module = ModuleFacade::find($moduleName);
                    if ($module && ! $module->isEnabled()) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    // Module system not available yet, continue with registration
                }

                if (
                    class_exists($panel)
                    && (! property_exists($panel, 'autoRegister') || (bool) ($panel::$autoRegister ?? true))
                ) {
                    $this->app->register($panel);
                }
            }
        };

        $registerPanels();

        // Ensure panels are picked up whether or not Filament is already resolved.
        $this->app->beforeResolving('filament', $registerPanels);
        $this->app->afterResolving('filament', $registerPanels);
    }

    public function packageBooted(): void
    {
        $this->attemptToRegisterModuleProviders();
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            $stubsPath = __DIR__ . '/../stubs/';
            foreach (app(Filesystem::class)->allFiles($stubsPath) as $file) {
                $relativePath = str($file->getPathname())->after($stubsPath)->toString();
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/modules/{$relativePath}"),
                ], 'modules-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsModules);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'coolsam/modules';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            Commands\ModuleFilamentInstallCommand::class,
            Commands\ModuleMakeFilamentClusterCommand::class,
            Commands\ModuleMakeFilamentPluginCommand::class,
            Commands\ModuleMakeFilamentResourceCommand::class,
            Commands\ModuleMakeFilamentPageCommand::class,
            Commands\ModuleMakeFilamentWidgetCommand::class,
            Commands\ModuleMakeFilamentThemeCommand::class,
            Commands\ModuleMakeFilamentPanelCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            //            'create_modules_table',
        ];
    }

    protected function registerModuleMacros(): void
    {
        Module::macro('namespace', function (?string $relativeNamespace = '') {
            $relativeNamespace = $relativeNamespace ?? '';
            $base = trim($this->app['config']->get('modules.namespace', 'Modules'), '\\');
            $relativeNamespace = trim($relativeNamespace, '\\');
            $studlyName = $this->getStudlyName();

            return str($base)->append('\\')->append($studlyName)->append('\\')->append($relativeNamespace)->replace('\\\\', '\\')->toString();
        });

        Module::macro('getTitle', function () {
            return str($this->getStudlyName())->kebab()->title()->replace('-', ' ')->toString();
        });

        Module::macro('appNamespace', function (string $relativeNamespace = '') {
            $prefix = str(config('modules.paths.app_folder', 'app'))->ltrim(DIRECTORY_SEPARATOR, '\\')->studly()->toString();
            $relativeNamespace = trim($relativeNamespace, '\\');
            if (filled($prefix)) {
                $relativeNamespace = str_replace($prefix . '\\', '', $relativeNamespace);
                $relativeNamespace = str_replace($prefix, '', $relativeNamespace);
            }

            return $this->namespace($relativeNamespace);
        });
        Module::macro('appPath', function (string $relativePath = '') {
            $appPath = $this->getExtraPath(config('modules.paths.app_folder', 'app'));

            return str($appPath . ($relativePath ? DIRECTORY_SEPARATOR . $relativePath : ''))->replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)->toString();
        });

        Module::macro('databasePath', function (string $relativePath = '') {
            $appPath = $this->getExtraPath('database');

            return str($appPath . ($relativePath ? DIRECTORY_SEPARATOR . $relativePath : ''))->replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)->toString();
        });

        Module::macro('resourcesPath', function (string $relativePath = '') {
            $appPath = $this->getExtraPath('resources');

            return str($appPath . ($relativePath ? DIRECTORY_SEPARATOR . $relativePath : ''))
                ->replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)->toString();
        });

        Module::macro('migrationsPath', function (string $relativePath = '') {
            $appPath = $this->databasePath('migrations');

            return str($appPath . ($relativePath ? DIRECTORY_SEPARATOR . $relativePath : ''))
                ->replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)->toString();
        });

        Module::macro('seedersPath', function (string $relativePath = '') {
            $appPath = $this->databasePath('seeders');

            return str($appPath . ($relativePath ? DIRECTORY_SEPARATOR . $relativePath : ''))->replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)->toString();
        });

        Module::macro('factoriesPath', function (string $relativePath = '') {
            $appPath = $this->databasePath('factories');

            return str($appPath . ($relativePath ? DIRECTORY_SEPARATOR . $relativePath : ''))->replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)->toString();
        });
    }

    /**
     * @return array<string>
     */
    protected function discoverPanelProviders(): array
    {
        $modulesPath = config('modules.paths.modules', 'Modules');
        $glob = rtrim($modulesPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . 'Filament' . DIRECTORY_SEPARATOR . '*PanelProvider.php';
        $panelProviders = glob($glob) ?: [];

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => env('FM_DEBUG_RUN', 'run3'),
                'hypothesisId' => 'H4',
                'location' => 'ModulesServiceProvider::discoverPanelProviders',
                'message' => 'panel provider discovery',
                'data' => [
                    'glob' => $glob,
                    'count' => count($panelProviders),
                    'providers' => array_values($panelProviders),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // swallow
        }
        // #endregion agent log

        return collect($panelProviders)
            ->map(fn($path) => FilamentModules::convertPathToNamespace($path))
            ->unique()
            ->values()
            ->toArray();
    }
}
