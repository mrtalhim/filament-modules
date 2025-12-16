<?php

namespace Coolsam\Modules\Commands;

use Coolsam\Modules\Concerns\GeneratesModularFiles;
use Coolsam\Modules\Concerns\HandlesNonInteractiveMode;
use Coolsam\Modules\Facades\FilamentModules;
use Filament\Commands\MakeClusterCommand;
use Illuminate\Support\Arr;
use Nwidart\Modules\Facades\Module;

use function Laravel\Prompts\search;
use function Laravel\Prompts\select;

class ModuleMakeFilamentClusterCommand extends MakeClusterCommand
{
    use GeneratesModularFiles;
    use HandlesNonInteractiveMode;

    protected $name = 'module:make:filament-cluster';

    protected $description = 'Create a new Filament cluster class in the module';

    protected $aliases = [
        'module:filament:make-cluster',
        'module:filament:cluster',
    ];

    protected function getRelativeNamespace(): string
    {
        return 'Filament\\Clusters';
    }

    protected function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            new \Symfony\Component\Console\Input\InputOption(
                name: 'namespace',
                shortcut: null,
                mode: \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                description: 'The namespace for the cluster',
            ),
        ]);
    }

    public function handle(): int
    {
        $this->ensureModuleArgument();
        $this->ensurePanel();

        return parent::handle();
    }

    public function ensurePanel(): void
    {
        if (! $this->option('panel')) {
            $panels = FilamentModules::getModulePanels($this->argument('module'));
            $defaultPanel = filament()->getDefaultPanel();
            
            if ($this->isNonInteractive()) {
                $this->input->setOption('panel', $defaultPanel->getId());
                return;
            }
            
            $options = collect([
                $defaultPanel,
                ...$panels,
            ])->mapWithKeys(function ($panel) {
                return [$panel->getId() => $panel->getId()];
            })->toArray();

            $panel = select('Please select the panel to create the cluster in:', $options);
            if (! $panel) {
                $this->error('No panel selected. Aborting cluster creation.');
                exit(1);
            }
            $this->input->setOption('panel', $panel);
        }
    }

    protected function configureClustersLocation(): void
    {
        $modulePanels = FilamentModules::getModulePanels($this->argument('module'));
        // Check if the panel is in the module
        $inModule = collect($modulePanels)->first(fn ($panel) => $panel->getId() === $this->panel->getId());
        if ($inModule) {
            $directories = $this->panel->getClusterDirectories();
            $namespaces = $this->panel->getClusterNamespaces();
        } else {
            // Get the default Cluster directories and namespaces in the module
            $directories = [$this->getModule()->appPath('Filament/Clusters/')];
            $namespaces = [$this->getModule()->appNamespace('Filament\\Clusters')];
        }

        foreach ($directories as $index => $directory) {
            if (str($directory)->startsWith(base_path('vendor'))) {
                unset($directories[$index]);
                unset($namespaces[$index]);
            }
        }

        if (count($namespaces) < 2) {
            $this->clustersNamespace = (Arr::first($namespaces) ?? $this->getModule()->appNamespace('Filament\\Clusters'));
            $this->clustersDirectory = (Arr::first($directories) ?? $this->getModule()->appPath('Filament' . DIRECTORY_SEPARATOR . 'Clusters' . DIRECTORY_SEPARATOR));

            return;
        }

        $keyedNamespaces = array_combine(
            $namespaces,
            $namespaces,
        );

        $providedNamespace = $this->option('namespace');
        if ($providedNamespace && in_array($providedNamespace, $namespaces)) {
            $this->clustersNamespace = $providedNamespace;
        } elseif ($this->isNonInteractive()) {
            $this->clustersNamespace = Arr::first($namespaces);
        } else {
            $this->clustersNamespace = search(
                label: 'Which namespace would you like to create this cluster in?',
                options: function (?string $search) use ($keyedNamespaces): array {
                    if (blank($search)) {
                        return $keyedNamespaces;
                    }

                    $search = str($search)->trim()->replace(['\\', '/'], '');

                    return array_filter($keyedNamespaces, fn (string $namespace): bool => str($namespace)->replace(['\\', '/'], '')->contains($search, ignoreCase: true));
                },
            );
        }
        $this->clustersDirectory = $directories[array_search($this->clustersNamespace, $namespaces)];
    }
}
