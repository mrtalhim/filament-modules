<?php

use Coolsam\Modules\Page;
use Nwidart\Modules\Facades\Module;

test('Page canAccess returns true when module is enabled', function () {
    // Create a mock module that's enabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(true);

    Module::shouldReceive('find')
        ->with('pagemodule')
        ->andReturn($mockModule);

    $page = new class extends Page
    {
        public static function getCurrentModuleName(): string
        {
            return 'pagemodule';
        }
    };

    expect($page::canAccess())->toBeTrue();
});

test('Page canAccess returns false when module is disabled', function () {
    // Create a mock module that's disabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(false);

    Module::shouldReceive('find')
        ->with('disabledpage')
        ->andReturn($mockModule);

    $page = new class extends Page
    {
        public static function getCurrentModuleName(): string
        {
            return 'disabledpage';
        }
    };

    expect($page::canAccess())->toBeFalse();
});
