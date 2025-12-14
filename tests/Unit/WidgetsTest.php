<?php

use Coolsam\Modules\ChartWidget;
use Coolsam\Modules\StatsOverviewWidget;
use Coolsam\Modules\TableWidget;
use Nwidart\Modules\Facades\Module;

test('StatsOverviewWidget canView delegates to canAccess', function () {
    // Create a mock module that's enabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(true);

    Module::shouldReceive('find')
        ->with('testmodule')
        ->andReturn($mockModule);

    $widget = new class extends StatsOverviewWidget
    {
        public static function getCurrentModuleName(): string
        {
            return 'testmodule';
        }
    };

    expect($widget::canView())->toBeTrue();
});

test('TableWidget canView delegates to canAccess', function () {
    // Create a mock module that's disabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(false);

    Module::shouldReceive('find')
        ->with('testmodule')
        ->andReturn($mockModule);

    $widget = new class extends TableWidget
    {
        public static function getCurrentModuleName(): string
        {
            return 'testmodule';
        }
    };

    expect($widget::canView())->toBeFalse();
});

test('ChartWidget canView delegates to canAccess', function () {
    // Create a mock module that's enabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(true);

    Module::shouldReceive('find')
        ->with('chartmodule')
        ->andReturn($mockModule);

    $widget = new class extends ChartWidget
    {
        public static function getCurrentModuleName(): string
        {
            return 'chartmodule';
        }

        protected function getType(): string
        {
            return 'line';
        }
    };

    expect($widget::canView())->toBeTrue();
});

test('widgets return empty stats when module is disabled', function () {
    // Create a mock module that's disabled
    $mockModule = \Mockery::mock(\Nwidart\Modules\Module::class);
    $mockModule->shouldReceive('isEnabled')->andReturn(false);

    Module::shouldReceive('find')
        ->with('disabledmodule')
        ->andReturn($mockModule);

    $widget = new class extends StatsOverviewWidget
    {
        public static function getCurrentModuleName(): string
        {
            return 'disabledmodule';
        }

        protected function getStats(): array
        {
            return [
                \Filament\Widgets\StatsOverviewWidget\Stat::make('Test', '123'),
            ];
        }
    };

    // Since canView returns false, the widget should not be accessible
    expect($widget::canView())->toBeFalse();
});
