<?php

namespace Coolsam\Modules\Commands;

use Coolsam\Modules\Concerns\GeneratesModularFiles;
use Coolsam\Modules\Enums\ConfigMode;
use Coolsam\Modules\Facades\FilamentModules;
use Filament\Commands\MakeResourceCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\search;
use function Laravel\Prompts\select;

class ModuleMakeFilamentResourceCommand extends MakeResourceCommand
{
    use GeneratesModularFiles;

    protected $name = 'module:make:filament-resource';

    protected $description = 'Create a new Filament resource class in the specified module';

    protected string $type = 'Resource';

    protected $aliases = [
        'module:filament:resource',
        'module:filament:make-resource',
    ];

    protected function getDefaultStubPath(): string
    {
        return base_path('vendor/filament/filament/stubs');
    }

    protected function getRelativeNamespace(): string
    {
        return 'Filament\\Resources';
    }

    public function handle(): int
    {
        $this->ensureModuleArgument();
        $modelFqn = $this->ensureModelNamespace();
        $this->ensureModelExists($modelFqn);
        $this->ensurePanel();

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run2',
                'hypothesisId' => 'H1',
                'location' => 'ModuleMakeFilamentResourceCommand::handle',
                'message' => 'pre-parent-handle',
                'data' => [
                    'module' => $this->argument('module'),
                    'model' => $this->argument('model'),
                    'modelFqn' => $modelFqn,
                    'resourceNamespaceOption' => $this->option('resource-namespace'),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // swallow
        }
        // #endregion agent log

        return parent::handle();
    }

    public function ensureModuleArgument(): void
    {
        if (! $this->argument('module')) {
            $module = select('Please select the module to create the resource in:', Module::allEnabled());
            if (! $module) {
                $this->error('No module selected. Aborting resource creation.');
                exit(1);
            }
            $this->input->setArgument('module', $module);
        }
    }

    public function ensureModelNamespace(): string
    {
        $modelNamespaceOption = $this->input->getOption('model-namespace');
        $modelInput = $this->input->getArgument('model') ?? $this->guessModelFromResourceName();

        if (! $modelInput) {
            $modelInput = select(
                'Please select the model within this module for the resource:',
                $this->possibleFqnModels()
            );
        }

        if (! $modelInput) {
            $this->error('No model namespace selected. Aborting resource creation.');
            exit(1);
        }

        $isFqn = $this->option('model-fqn') || str($modelInput)->contains('\\');

        if ($isFqn) {
            $modelFqn = ltrim($modelInput, '\\');
            $modelNamespace = str($modelFqn)->beforeLast('\\')->toString();
            $modelClass = class_basename($modelFqn);

            // Preserve the provided FQN by splitting into namespace + class for the parent command.
            $this->input->setArgument('model', $modelClass);
            $this->input->setOption('model-namespace', $modelNamespace);
            $this->output->info("Using model: {$modelFqn}");

            return $modelFqn;
        }

        $modelClass = class_basename($modelInput);
        $modelNamespace = trim($modelNamespaceOption ?: $this->getModule()->appNamespace('Models'), '\\');
        $modelFqn = $modelNamespace . '\\' . $modelClass;

        $this->input->setOption('model-namespace', $modelNamespace);
        $this->input->setArgument('model', $modelClass);

        $this->output->info("Using model namespace: {$modelNamespace}");
        $this->output->info("Using model name: {$modelClass}");

        return $modelFqn;
    }

    public function ensurePanel()
    {
        $defaultPanel = filament()->getDefaultPanel();
        if (! FilamentModules::getMode()->shouldRegisterPanels()) {
            $this->panel = $defaultPanel;
        } else {
            $modulePanels = FilamentModules::getModulePanels($this->getModule());
            if (count($modulePanels) === 0) {
                $this->panel = $defaultPanel;

                return;
            }
            $options = [
                $defaultPanel->getId(),
                ...collect($modulePanels)->map(fn ($panel) => $panel->getId())->values()->all(),
            ];
            $panelId = select(
                label: 'Please select the Filament panel to create the resource in:',
                options: $options,
                default: $defaultPanel->getId(),
            );
            $this->input->setOption('panel', $panelId);
            $this->panel = filament()->getPanel($panelId, isStrict: false);
            if (! $this->panel) {
                $this->error("Panel [{$panelId}] not found. Aborting resource creation.");
                exit(1);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function getResourcesLocation(string $question): array
    {
        $modulePanels = FilamentModules::getModulePanels($this->getModule());
        $mode = ConfigMode::tryFrom(config('filament-modules.mode', ConfigMode::BOTH->value));
        if ($mode->shouldRegisterPanels() && in_array($this->panel->getId(), collect($modulePanels)->map(fn ($panel) => $panel->getId())->all())) {
            $directories = $this->panel->getResourceDirectories();
            $namespaces = $this->panel->getResourceNamespaces();
        } else {
            // Default to the module's filament resources directory
            $directories = [
                $this->getModule()->appPath('Filament' . DIRECTORY_SEPARATOR . 'Resources'),
            ];
            $namespaces = [
                $this->getModule()->appNamespace('Filament\\Resources'),
            ];
        }

        foreach ($directories as $index => $directory) {
            if (str($directory)->startsWith(base_path('vendor'))) {
                unset($directories[$index]);
                unset($namespaces[$index]);
            }
        }

        if (count($namespaces) < 2) {
            $namespace = Arr::first($namespaces) ?? $this->getModule()->appNamespace('Filament\\Resources');
            $directory = Arr::first($directories) ?? $this->getModule()->appPath('Filament' . DIRECTORY_SEPARATOR . 'Resources');
            $this->input->setOption('resource-namespace', $namespace);

            // #region agent log
            try {
                $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
                app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
                $payload = json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run2',
                    'hypothesisId' => 'H1',
                    'location' => 'ModuleMakeFilamentResourceCommand::getResourcesLocation',
                    'message' => 'resolved single namespace',
                    'data' => [
                        'namespace' => $namespace,
                        'directory' => $directory,
                    ],
                    'timestamp' => round(microtime(true) * 1000),
                ]);
                file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
            } catch (\Throwable $e) {
            }
            // #endregion agent log

            return [
                $namespace,
                $directory,
            ];
        }

        if ($this->option('resource-namespace')) {
            return [
                (string) $this->option('resource-namespace'),
                $directories[array_search($this->option('resource-namespace'), $namespaces)],
            ];
        }

        $keyedNamespaces = array_combine(
            $namespaces,
            $namespaces,
        );

        $result = [
            $namespace = search(
                label: $question,
                options: function (?string $search) use ($keyedNamespaces): array {
                    if (blank($search)) {
                        return $keyedNamespaces;
                    }

                    $search = str($search)->trim()->replace(['\\', '/'], '');

                    return array_filter($keyedNamespaces, fn (string $namespace): bool => str($namespace)->replace(['\\', '/'], '')->contains($search, ignoreCase: true));
                },
            ),
            $directories[array_search($namespace, $namespaces)],
        ];

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run2',
                'hypothesisId' => 'H1',
                'location' => 'ModuleMakeFilamentResourceCommand::getResourcesLocation',
                'message' => 'resolved multi namespace',
                'data' => [
                    'namespace' => $result[0] ?? null,
                    'directory' => $result[1] ?? null,
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
        }
        // #endregion agent log

        return $result;
    }

    protected function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            new InputOption(
                name: 'model-fqn',
                mode: InputOption::VALUE_NONE,
                description: 'Treat the provided model argument as a fully-qualified class name.',
            ),
        ]);
    }

    protected function ensureModelExists(string $modelFqn): void
    {
        if (! $this->input->hasOption('model') || ! $this->option('model')) {
            return;
        }

        if (class_exists($modelFqn)) {
            return;
        }

        $moduleNamespace = $this->getModule()->namespace('');
        $relativePath = ltrim(str($modelFqn)->after($moduleNamespace)->toString(), '\\');
        $path = $this->getModule()->appPath(
            str_replace('\\', DIRECTORY_SEPARATOR, $relativePath) . '.php'
        );

        $filesystem = app(Filesystem::class);
        $filesystem->ensureDirectoryExists(pathinfo($path, PATHINFO_DIRNAME));
        $filesystem->put($path, $this->buildModelStub($modelFqn));

        $this->components->info("Model [{$modelFqn}] created at [{$path}]");

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run2',
                'hypothesisId' => 'H1',
                'location' => 'ModuleMakeFilamentResourceCommand::ensureModelExists',
                'message' => 'model created',
                'data' => [
                    'modelFqn' => $modelFqn,
                    'path' => $path,
                    'exists_after' => file_exists($path),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // swallow
        }
        // #endregion agent log
    }

    protected function buildModelStub(string $modelFqn): string
    {
        $namespace = Str::beforeLast($modelFqn, '\\');
        $class = Str::afterLast($modelFqn, '\\');

        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class {$class} extends Model
{
    use HasFactory;
}

PHP;
    }

    protected function guessModelFromResourceName(): ?string
    {
        $resourceName = $this->input->getArgument('name');
        if (! $resourceName) {
            return null;
        }

        return str_replace('Resource', '', class_basename($resourceName));
    }
}
