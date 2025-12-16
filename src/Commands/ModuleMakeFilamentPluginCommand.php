<?php

namespace Coolsam\Modules\Commands;

use Coolsam\Modules\Concerns\GeneratesModularFiles;
use Coolsam\Modules\Concerns\HandlesNonInteractiveMode;
use Illuminate\Console\GeneratorCommand;

class ModuleMakeFilamentPluginCommand extends GeneratorCommand
{
    use GeneratesModularFiles;
    use HandlesNonInteractiveMode;

    protected $name = 'module:make:filament-plugin';

    protected $description = 'Create a new Filament Plugin class in the module';

    protected $type = 'Filament Plugin';

    protected $aliases = [
        'module:filament:plugin',
        'module:filament:make-plugin',
    ];

    protected function getRelativeNamespace(): string
    {
        return 'Filament';
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/filament-plugin.stub');
    }

    protected function stubReplacements(): array
    {
        return [
            'moduleStudlyName' => $this->getModule()->getStudlyName(),
            'pluginId' => str($this->argument('name'))->replace('Plugin', '')->studly()->lower()->toString(),
        ];
    }

    public function handle(): ?bool
    {
        $this->ensureModuleArgument();

        return parent::handle();
    }
}
