<?php

namespace Coolsam\Modules\Commands;

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
        $interactive = $this->input->isInteractive() && ! app()->runningUnitTests();
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
        $shouldCreateDefaultCluster = $this->cluster && ($interactive ? confirm('Would you like to create a default Cluster for the module?', true) : false);
        if ($shouldCreateDefaultCluster) {
            $this->createDefaultFilamentCluster();
        }

        // TODO: Support creation of panels
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
            if (confirm("Module $this->moduleName does not exist. Would you like to generate it?", true)) {
                $this->call('module:make', ['name' => [$this->moduleName]]);

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
            $filesystem->copy($from, $to);
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
}
