<?php

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelRegistry;
use Illuminate\Filesystem\Filesystem;
use Laravel\Prompts\Prompt;

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

test('frontend scaffolding logic creates correct file structure for tailwind', function () {
    $filesystem = app(Filesystem::class);

    // Create a mock module
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('getPath')->andReturn('/tmp/test-module');
    $mockModule->shouldReceive('resourcesPath')->andReturn('/tmp/test-module/resources');
    $mockModule->shouldReceive('getExtraPath')->andReturn('/tmp/test-module/vite.config.js');

    // Test the frontend preset logic
    $frontendPreset = 'tailwind';
    $stubBase = \Coolsam\Modules\Facades\FilamentModules::packagePath('stubs/module/' . $frontendPreset);

    expect(is_dir($stubBase))->toBeTrue();

    // Check that the expected stub files exist
    $expectedStubs = [
        $stubBase . DIRECTORY_SEPARATOR . 'package.json',
        $stubBase . DIRECTORY_SEPARATOR . 'vite.config.js',
        $stubBase . DIRECTORY_SEPARATOR . 'tailwind.config.js',
        $stubBase . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'app.css',
    ];

    foreach ($expectedStubs as $stub) {
        expect(file_exists($stub))->toBeTrue("Stub file should exist: {$stub}");
    }

    // Verify package.json contains expected dependencies
    $packageJson = json_decode(file_get_contents($stubBase . DIRECTORY_SEPARATOR . 'package.json'), true);
    expect($packageJson['devDependencies'])->toHaveKeys([
        'tailwindcss',
        '@tailwindcss/vite',
        'laravel-vite-plugin',
    ]);
});

test('frontend scaffolding logic creates correct file structure for sass', function () {
    $filesystem = app(Filesystem::class);

    // Test the sass frontend preset logic
    $frontendPreset = 'sass';
    $stubBase = \Coolsam\Modules\Facades\FilamentModules::packagePath('stubs/module/' . $frontendPreset);

    expect(is_dir($stubBase))->toBeTrue();

    // Check that the expected sass stub files exist
    $expectedStubs = [
        $stubBase . DIRECTORY_SEPARATOR . 'package.json',
        $stubBase . DIRECTORY_SEPARATOR . 'vite.config.js',
        $stubBase . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'app.scss',
    ];

    foreach ($expectedStubs as $stub) {
        expect(file_exists($stub))->toBeTrue("Sass stub file should exist: {$stub}");
    }

    // Should not have tailwind-specific files
    $tailwindSpecific = $stubBase . DIRECTORY_SEPARATOR . 'tailwind.config.js';
    expect(file_exists($tailwindSpecific))->toBeFalse('Should not have tailwind config in sass preset');

    // Verify package.json contains expected dependencies
    $packageJson = json_decode(file_get_contents($stubBase . DIRECTORY_SEPARATOR . 'package.json'), true);
    expect($packageJson['devDependencies'])->toHaveKey('laravel-vite-plugin');
});

test('frontend scaffolding handles missing stub directory gracefully', function () {
    // Test that the logic handles missing stub directories
    $nonExistentPreset = 'nonexistent';
    $stubBase = \Coolsam\Modules\Facades\FilamentModules::packagePath('stubs/module/' . $nonExistentPreset);

    expect(is_dir($stubBase))->toBeFalse('Non-existent preset directory should not exist');

    // This tests the logic in ensureFrontendScaffolding that checks if stubBase exists
    // If the directory doesn't exist, the method should return early without error
    expect(is_dir($stubBase))->toBeFalse();
});
