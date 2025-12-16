<?php

namespace Coolsam\Modules\Commands;

use Coolsam\Modules\Commands\FileGenerators\ModulePanelProviderClassGenerator;
use Coolsam\Modules\Concerns\GeneratesModularFiles;
use Coolsam\Modules\Concerns\HandlesNonInteractiveMode;
use Filament\Commands\MakePanelCommand;
use Filament\Support\Commands\Concerns\CanGeneratePanels;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Exceptions\FailureCommandOutput;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ModuleMakeFilamentPanelCommand extends MakePanelCommand
{
    use CanGeneratePanels;
    use CanManipulateFiles;
    use GeneratesModularFiles;
    use HandlesNonInteractiveMode;

    protected $name = 'module:make:filament-panel';

    protected $description = 'Create a new Filament panel class in the specified module';

    /**
     * @var array<string>
     */
    protected $aliases = [
        'module:filament:make-panel',
        'module:filament:panel',
    ];

    /**
     * @return array<InputArgument>
     */
    protected function getArguments(): array
    {
        return [
            new InputArgument(
                name: 'id',
                mode: InputArgument::OPTIONAL,
                description: 'The ID of the panel',
            ),
            new InputArgument(
                name: 'module',
                mode: InputArgument::OPTIONAL,
                description: 'The module to create the panel in',
            ),
        ];
    }

    /**
     * @return array<InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption(
                name: 'force',
                shortcut: 'F',
                mode: InputOption::VALUE_NONE,
                description: 'Overwrite the contents of the files if they already exist',
            ),
            new InputOption(
                name: 'label',
                shortcut: null,
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The navigation label for the panel',
            ),
            new InputOption(
                name: 'no-auto-register-panel',
                shortcut: null,
                mode: InputOption::VALUE_NONE,
                description: 'Generate the panel provider without auto-registering it via ModulesServiceProvider',
            ),
        ];
    }

    public function handle(): int
    {
        try {
            $this->ensureModuleArgument();
            $this->generatePanel(
                id: $this->argument('id'),
                placeholderId: 'default',
                isForced: $this->option('force'),
            );
        } catch (FailureCommandOutput) {
            return static::FAILURE;
        }

        return static::SUCCESS;
    }

    protected function ensureNavigationLabelOption(): void
    {
        if (! $this->option('label')) {
            if ($this->isNonInteractive()) {
                $defaultLabel = Str::title($this->argument('id') ?? $this->getModule()->getName() . ' App');
                $this->input->setOption('label', $defaultLabel);
                return;
            }
            $label = text(
                label: 'What is the navigation label for the panel?',
                placeholder: Str::title($this->argument('id') ?? $this->getModule()->getName() . ' App'),
                required: true,
                validate: fn (string $value) => empty($value) ? 'The navigation label cannot be empty.' : null,
                hint: 'This is used in the navigation to identify the panel.',
            );
            if (empty($label)) {
                $this->components->error('Navigation label cannot be empty. Aborting panel creation.');
                exit(1);
            }
            $this->input->setOption('label', $label);
        }
    }

    protected function getRelativeNamespace(): string
    {
        return 'Providers\\Filament';
    }

    /**
     * @throws FailureCommandOutput
     */
    public function generatePanel(?string $id = null, string $defaultId = '', string $placeholderId = '', bool $isForced = false): void
    {
        $module = $this->getModule();
        $this->components->info("Creating Filament panel in module [{$module->getName()}]...");
        
        if (! $id) {
            if ($this->isNonInteractive()) {
                $id = $placeholderId ?: 'default';
                if (empty($id)) {
                    $this->errorNonInteractive(
                        'Panel ID is required in non-interactive mode.',
                        'php artisan module:make:filament-panel <id> <module>'
                    );
                }
            } else {
                $id = text(
                    label: 'What is the panel\'s ID?',
                    placeholder: $placeholderId,
                    required: true,
                    validate: fn (string $value) => match (true) {
                        preg_match('/^[a-zA-Z].*/', $value) !== false => null,
                        default => 'The ID must start with a letter, and not a number or special character.',
                    },
                    hint: 'It must be unique to any others you have, and is used to reference the panel in your code.',
                );
            }
        }
        
        $id = Str::lcfirst($id);
        if (empty($id)) {
            $this->components->error('Panel ID cannot be empty. Aborting panel creation.');
            exit(1);
        }
        $this->ensureNavigationLabelOption();

        $basename = (string) str($id)
            ->studly()
            ->append('PanelProvider');

        $path = $module->appPath(
            (string) str($basename)
                ->prepend('/Providers/Filament/')
                ->replace('\\', '/')
                ->append('.php'),
        );
        app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(pathinfo($path, PATHINFO_DIRNAME));

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run3',
                'hypothesisId' => 'H4',
                'location' => 'ModuleMakeFilamentPanelCommand::generatePanel',
                'message' => 'panel target',
                'data' => [
                    'module' => $module->getName(),
                    'path' => $path,
                    'fqn' => $module->appNamespace("Providers\\Filament\\{$basename}"),
                    'id' => $id,
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
        }
        // #endregion agent log

        if (! $isForced && $this->checkForCollision([$path])) {
            throw new FailureCommandOutput;
        }

        $fqn = $module->appNamespace("Providers\\Filament\\{$basename}");

        $this->writeFile($path, app(ModulePanelProviderClassGenerator::class, [
            'fqn' => $fqn,
            'id' => $id,
            'moduleName' => $module->getName(),
            'navigationLabel' => $this->option('label'),
            'autoRegister' => ! $this->option('no-auto-register-panel'),
        ]));

        // Eagerly register the provider when auto-registration is enabled so tests/users see it immediately.
        if (config('filament-modules.auto_register_panels', true) && ! $this->option('no-auto-register-panel')) {
            if (! class_exists($fqn) && file_exists($path)) {
                require_once $path;
            }

            if (class_exists($fqn)) {
                $this->laravel->register($fqn);

                // Also register the Panel instance immediately so Filament can discover it in the same request.
                try {
                    $providerInstance = new $fqn($this->laravel);
                    if (method_exists($providerInstance, 'panel')) {
                        $panelInstance = $providerInstance->panel(\Filament\Panel::make());
                        app(\Filament\PanelRegistry::class)->register($panelInstance);

                        // #region agent log
                        try {
                            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
                            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
                            $payload = json_encode([
                                'sessionId' => 'debug-session',
                                'runId' => 'run3',
                                'hypothesisId' => 'H4',
                                'location' => 'ModuleMakeFilamentPanelCommand::generatePanel',
                                'message' => 'panel instance registered',
                                'data' => [
                                    'ids' => collect(\Filament\Facades\Filament::getPanels())->map(fn ($panel) => $panel->getId())->values()->all(),
                                    'targetId' => $panelInstance->getId(),
                                ],
                                'timestamp' => round(microtime(true) * 1000),
                            ]);
                            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
                        } catch (\Throwable $e) {
                        }
                        // #endregion agent log
                    }
                } catch (\Throwable $e) {
                    // #region agent log
                    try {
                        $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
                        app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
                        $payload = json_encode([
                            'sessionId' => 'debug-session',
                            'runId' => 'run3',
                            'hypothesisId' => 'H4',
                            'location' => 'ModuleMakeFilamentPanelCommand::generatePanel',
                            'message' => 'panel instance registration error',
                            'data' => [
                                'fqn' => $fqn,
                                'error' => $e->getMessage(),
                            ],
                            'timestamp' => round(microtime(true) * 1000),
                        ]);
                        file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
                    } catch (\Throwable $inner) {
                    }
                    // swallow; discovery will still pick it up later
                }
            }

            // #region agent log
            try {
                $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
                app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
                $payload = json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run3',
                    'hypothesisId' => 'H4',
                    'location' => 'ModuleMakeFilamentPanelCommand::generatePanel',
                    'message' => 'panel registered',
                    'data' => [
                        'fqn' => $fqn,
                        'registered' => ! empty(app()->getProviders($fqn)),
                    ],
                    'timestamp' => round(microtime(true) * 1000),
                ]);
                file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
            } catch (\Throwable $e) {
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
                'hypothesisId' => 'H4',
                'location' => 'ModuleMakeFilamentPanelCommand::generatePanel',
                'message' => 'panel written',
                'data' => [
                    'path' => $path,
                    'exists_after' => file_exists($path),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
        }
        // #endregion agent log

        $this->components->info("Filament panel [{$path}] created successfully.");
    }
}
