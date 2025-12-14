<?php

use Coolsam\Modules\Resource;
use Nwidart\Modules\Facades\Module;

test('Resource canAccess returns true when module is enabled', function () {
    // Create a mock module that's enabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(true);

    Module::shouldReceive('find')
        ->with('resourcemodule')
        ->andReturn($mockModule);

    $resource = new class extends Resource
    {
        protected static ?string $model = null;

        public static function getCurrentModuleName(): string
        {
            return 'resourcemodule';
        }
    };

    expect($resource::canAccess())->toBeTrue();
});

test('Resource canAccess returns false when module is disabled', function () {
    // Create a mock module that's disabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(false);

    Module::shouldReceive('find')
        ->with('disabledresource')
        ->andReturn($mockModule);

    $resource = new class extends Resource
    {
        protected static ?string $model = null;

        public static function getCurrentModuleName(): string
        {
            return 'disabledresource';
        }
    };

    expect($resource::canAccess())->toBeFalse();
});

test('Resource canAccess returns false when module does not exist', function () {
    Module::shouldReceive('find')
        ->with('nonexistentresource')
        ->andReturn(null);

    $resource = new class extends Resource
    {
        protected static ?string $model = null;

        public static function getCurrentModuleName(): string
        {
            return 'nonexistentresource';
        }
    };

    expect($resource::canAccess())->toBeFalse();
});
