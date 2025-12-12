<?php

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelRegistry;
use Laravel\Prompts\Prompt;
use Illuminate\Filesystem\Filesystem;

use function Pest\Laravel\artisan;

function makeModule(string $name): string
{
    $filesystem = app(Filesystem::class);
    $modulesPath = rtrim(config('modules.paths.modules'), DIRECTORY_SEPARATOR);
    $modulePath = $modulesPath . DIRECTORY_SEPARATOR . $name;
    $filesystem->deleteDirectory($modulePath);
    $filesystem->ensureDirectoryExists($modulePath . '/app/Providers/Filament');
    $filesystem->ensureDirectoryExists($modulePath . '/app/Models');
    $filesystem->put($modulePath . '/module.json', json_encode([
        'name' => $name,
        'alias' => str($name)->kebab()->toString(),
        'description' => '',
        'priority' => 0,
        'providers' => [
            "Modules\\{$name}\\Providers\\{$name}ServiceProvider",
        ],
        'files' => [],
    ], JSON_PRETTY_PRINT));
    $filesystem->put($modulePath . '/composer.json', json_encode([
        'name' => "modules/" . str($name)->kebab(),
        'autoload' => [
            'psr-4' => [
                "Modules\\\\{$name}\\\\\\" => 'app/',
            ],
        ],
    ], JSON_PRETTY_PRINT));
    $providerPath = $modulePath . "/app/Providers/{$name}ServiceProvider.php";
    $filesystem->ensureDirectoryExists(dirname($providerPath));
    $filesystem->put($providerPath, <<<PHP
<?php

namespace Modules\\{$name}\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void {}
}

PHP);

    // #region agent log
    try {
        $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
        app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
        $payload = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'run2',
            'hypothesisId' => 'H5',
            'location' => 'GeneratorsTest::makeModule',
            'message' => 'module scaffolded',
            'data' => [
                'module' => $name,
                'modulePath' => $modulePath,
                'statusesFile' => config('modules.activators.file.statuses-file'),
                'modulesPath' => $modulesPath,
            ],
            'timestamp' => round(microtime(true) * 1000),
        ]);
        file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
    } catch (\Throwable $e) {
        // swallow
    }
    // #endregion agent log

    // mark module enabled
    try {
        $statusesPath = config('modules.activators.file.statuses-file');
        $filesystem->ensureDirectoryExists(dirname($statusesPath));
        $existing = [];
        if (file_exists($statusesPath)) {
            $decoded = json_decode(file_get_contents($statusesPath), true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }
        $existing[$name] = true;
        $result = file_put_contents($statusesPath, json_encode($existing, JSON_PRETTY_PRINT));

        $rootStatuses = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'modules_statuses.json';
        file_put_contents($rootStatuses, json_encode($existing, JSON_PRETTY_PRINT));

        // #region agent log
        try {
            $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
            app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run2',
                'hypothesisId' => 'H5',
                'location' => 'GeneratorsTest::makeModule',
                'message' => 'statuses write',
                'data' => [
                    'path' => $statusesPath,
                    'rootPath' => $rootStatuses,
                    'result' => $result,
                    'contents' => $existing,
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
        }
        // #endregion agent log
    } catch (\Throwable $e) {
        // swallow
    }

    return $modulePath;
}

beforeEach(function () {
    $filesystem = app(Filesystem::class);
    $modulesPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'workbench' . DIRECTORY_SEPARATOR . 'Modules';
    $filesystem->deleteDirectory($modulesPath);
    $filesystem->ensureDirectoryExists($modulesPath);

    config()->set('modules.paths.modules', $modulesPath);
    config()->set('modules.paths.app_folder', 'app');
    config()->set('modules.namespace', 'Modules');
    $statusesFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'workbench' . DIRECTORY_SEPARATOR . 'modules_statuses.json';
    $filesystem->put($statusesFile, json_encode([]));
    $filesystem->put(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'modules_statuses.json', json_encode([]));
    config()->set('modules.activators.file.statuses-file', $statusesFile);
    config()->set('modules.scan.enabled', false);
    Prompt::interactive(false);
    $this->withoutMockingConsoleOutput();
    $panel = Panel::make()->id('admin')->path('admin')->default();
    $registry = app(PanelRegistry::class);
    $registry->register($panel);
    $registry->defaultPanel = $panel;
    Filament::setCurrentPanel($panel);
});

test('resource generator creates model and correct namespace when --model is provided', function () {
    makeModule('TestModule');

    $exit = artisan('module:make:filament-resource', [
        'model' => 'TestProduct',
        'module' => 'TestModule',
        '--model' => true,
        '--no-interaction' => true,
    ]);

    // #region agent log
    try {
        $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
        app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
        $payload = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'run2',
            'hypothesisId' => 'H1',
            'location' => 'GeneratorsTest::resource generator cmd',
            'message' => 'artisan exit',
            'data' => ['exit' => $exit],
            'timestamp' => round(microtime(true) * 1000),
        ]);
        file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
    } catch (\Throwable $e) {
    }
    // #endregion agent log

    expect($exit)->toBe(0);

    $moduleBase = rtrim(config('modules.paths.modules'), DIRECTORY_SEPARATOR);
    $modelPath = $moduleBase . '/TestModule/app/Models/TestProduct.php';
    expect($modelPath)->toBeFile();
    expect(file_get_contents($modelPath))->toContain('namespace Modules\\TestModule\\Models;');

    $resourcePath = $moduleBase . '/TestModule/app/Filament/Resources/TestProducts/TestProductResource.php';
    expect($resourcePath)->toBeFile();
    expect(file_get_contents($resourcePath))->toContain('use Modules\\TestModule\\Models\\TestProduct;');
});

test('resource generator preserves fully qualified model argument', function () {
    makeModule('FqnModule');

    $exit = artisan('module:make:filament-resource', [
        'model' => 'App\\Domain\\Orders\\Order',
        'module' => 'FqnModule',
        '--model-fqn' => true,
        '--no-interaction' => true,
    ]);
    try {
        $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
        app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
        $payload = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'run2',
            'hypothesisId' => 'H1',
            'location' => 'GeneratorsTest::resource generator fqn cmd',
            'message' => 'artisan exit',
            'data' => ['exit' => $exit],
            'timestamp' => round(microtime(true) * 1000),
        ]);
        file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
    } catch (\Throwable $e) {
    }
    expect($exit)->toBe(0);

    $moduleBase = rtrim(config('modules.paths.modules'), DIRECTORY_SEPARATOR);
    $resourcePath = $moduleBase . '/FqnModule/app/Filament/Resources/Orders/OrderResource.php';
    expect($resourcePath)->toBeFile();
    expect(file_get_contents($resourcePath))->toContain('use App\\Domain\\Orders\\Order;');
});

test('panel provider auto-registers without manual bootstrap edits', function () {
    makeModule('PanelModule');

    $exit = artisan('module:make:filament-panel', [
        'id' => 'AdminArea',
        'module' => 'PanelModule',
        '--label' => 'Admin Area',
        '--no-interaction' => true,
    ]);
    try {
        $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
        app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
        $payload = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'run2',
            'hypothesisId' => 'H4',
            'location' => 'GeneratorsTest::panel cmd',
            'message' => 'artisan exit',
            'data' => ['exit' => $exit],
            'timestamp' => round(microtime(true) * 1000),
        ]);
        file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
    } catch (\Throwable $e) {
    }
    expect($exit)->toBe(0);

    $provider = 'Modules\\PanelModule\\Providers\\Filament\\AdminAreaPanelProvider';
    $panelId = 'panel-module-admin-area';

    Filament::getPanels(); // Triggers beforeResolving callback that registers panels

    expect(app()->getProviders($provider))->not()->toBeEmpty();
    expect(Filament::getPanel($panelId, isStrict: false))->not()->toBeNull();
});

test('module install scaffolds tailwind + vite defaults', function () {
    makeModule('TailwindModule');

    $exit = artisan('module:filament:install', [
        'module' => 'TailwindModule',
        '--no-interaction' => true,
    ]);
    try {
        $logPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'debug.log';
        app(\Illuminate\Filesystem\Filesystem::class)->ensureDirectoryExists(dirname($logPath));
        $payload = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'run2',
            'hypothesisId' => 'H3',
            'location' => 'GeneratorsTest::install cmd',
            'message' => 'artisan exit',
            'data' => ['exit' => $exit],
            'timestamp' => round(microtime(true) * 1000),
        ]);
        file_put_contents($logPath, $payload . PHP_EOL, FILE_APPEND);
    } catch (\Throwable $e) {
    }
    expect($exit)->toBe(0);

    $base = rtrim(config('modules.paths.modules'), DIRECTORY_SEPARATOR) . '/TailwindModule';
    $package = json_decode(file_get_contents($base . '/package.json'), true);

    expect($package['devDependencies'])->toHaveKeys([
        'tailwindcss',
        '@tailwindcss/vite',
        'laravel-vite-plugin',
    ]);

    $viteConfig = file_get_contents($base . '/vite.config.js');
    expect($viteConfig)->toContain('@tailwindcss/vite');
    expect(file_exists($base . '/tailwind.config.js'))->toBeTrue();
    expect(file_exists($base . '/resources/css/app.css'))->toBeTrue();
});

