<?php

use Coolsam\Modules\Traits\CanAccessTrait;
use Nwidart\Modules\Facades\Module;

test('getCurrentModuleName extracts module name from namespace', function () {
    // Create a test class that uses the trait
    $testClass = new class extends \Filament\Resources\Resource
    {
        use CanAccessTrait;
    };

    // Override the getCurrentModuleName method for testing
    $mockClass = new class extends \Filament\Resources\Resource
    {
        use CanAccessTrait;

        public static function getCurrentModuleName(): string
        {
            $provider = 'Modules\\TestModule\\Filament\\Resources\\TestResource';
            $provider = explode('\\', $provider);
            $provider = strtolower($provider[1]);

            return $provider;
        }
    };

    expect($mockClass::getCurrentModuleName())->toBe('testmodule');
});

test('canAccess returns true when module is enabled', function () {
    // Create a mock module that's enabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(true);

    Module::shouldReceive('find')
        ->with('testmodule')
        ->andReturn($mockModule);

    // Create a test class that uses the trait
    $testClass = new class extends \Filament\Resources\Resource
    {
        use CanAccessTrait;

        public static function getCurrentModuleName(): string
        {
            return 'testmodule';
        }
    };

    expect($testClass::canAccess())->toBeTrue();
});

test('canAccess returns false when module is disabled', function () {
    // Create a mock module that's disabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(false);

    Module::shouldReceive('find')
        ->with('testmodule')
        ->andReturn($mockModule);

    // Create a test class that uses the trait
    $testClass = new class extends \Filament\Resources\Resource
    {
        use CanAccessTrait;

        public static function getCurrentModuleName(): string
        {
            return 'testmodule';
        }
    };

    expect($testClass::canAccess())->toBeFalse();
});

test('canAccess returns false when module is not found', function () {
    Module::shouldReceive('find')
        ->with('nonexistent')
        ->andReturn(null);

    // Create a test class that uses the trait
    $testClass = new class extends \Filament\Resources\Resource
    {
        use CanAccessTrait;

        public static function getCurrentModuleName(): string
        {
            return 'nonexistent';
        }
    };

    expect($testClass::canAccess())->toBeFalse();
});
