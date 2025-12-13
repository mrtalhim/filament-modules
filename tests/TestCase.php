<?php

namespace Coolsam\Modules\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Coolsam\Modules\ModulesServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Panel;
use Filament\PanelRegistry;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Filesystem\Filesystem;
use Livewire\LivewireServiceProvider;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Coolsam\\Modules\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        // Ensure a default Filament panel exists for all tests.
        $panel = Panel::make()->id('admin')->path('admin')->default();
        $registry = app(PanelRegistry::class);
        $registry->register($panel);
        $registry->defaultPanel = $panel;
        Filament::setCurrentPanel($panel);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            LaravelModulesServiceProvider::class,
            ModulesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        $filesystem = app(Filesystem::class);
        $packageRoot = dirname(__DIR__, 1);
        $modulesPath = $packageRoot . DIRECTORY_SEPARATOR . 'workbench' . DIRECTORY_SEPARATOR . 'Modules';
        $filesystem->ensureDirectoryExists($modulesPath);
        config()->set('modules.paths.modules', $modulesPath);
        config()->set('modules.paths.app_folder', 'app');
        config()->set('modules.namespace', 'Modules');
        config()->set('modules.scan.enabled', false);
        $statusesFile = $packageRoot . DIRECTORY_SEPARATOR . 'workbench' . DIRECTORY_SEPARATOR . 'modules_statuses.json';
        $filesystem->put($statusesFile, json_encode([]));
        $filesystem->put($packageRoot . DIRECTORY_SEPARATOR . 'modules_statuses.json', json_encode([]));
        config()->set('modules.activators.file.statuses-file', $statusesFile);

        /*
        $migration = include __DIR__.'/../database/migrations/create_modules_table.php.stub';
        $migration->up();
        */
    }
}
