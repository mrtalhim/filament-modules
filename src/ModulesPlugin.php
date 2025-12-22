<?php

namespace Coolsam\Modules;

use Coolsam\Modules\Enums\ConfigMode;
use Coolsam\Modules\Facades\FilamentModules;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Nwidart\Modules\Facades\Module;

class ModulesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'modules';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->topNavigation(config('filament-modules.clusters.enabled', false) && config('filament-modules.clusters.use-top-navigation', false));
        $mode = ConfigMode::tryFrom(config('filament-modules.mode', ConfigMode::BOTH->value));
        if ($mode?->shouldRegisterPlugins()) {
            $plugins = $this->getModulePlugins();
            foreach ($plugins as $modulePlugin) {
                $panel->plugin($modulePlugin::make());
            }
        }
    }

    public function boot(Panel $panel): void
    {
        // Register panels
        $mode = ConfigMode::tryFrom(config('filament-modules.mode', ConfigMode::BOTH->value));
        if ($mode?->shouldRegisterPanels()) {
            $group = config('filament-modules.panels.group', 'Modules');
            $groupIcon = config('filament-modules.panels.group-icon', \Filament\Support\Icons\Heroicon::OutlinedRectangleStack);
            $groupSort = config('filament-modules.panels.group-sort', 0);
            $openInNewTab = config('filament-modules.panels.open-in-new-tab', false);

            $panels = $this->getModulePanels();
            $panel->navigationGroups([
                NavigationGroup::make($group)
                    ->icon($groupIcon)
                    ->collapsed(),
            ]);

            $navItems = collect($panels)->map(function (Panel $modulePanel) use ($group, $groupSort, $openInNewTab) {
                // Extract module name from panel ID (format: module-kebab-name-panel-id)
                // Need to find the correct module name by trying different prefixes
                $panelId = $modulePanel->getId();
                $parts = explode('-', $panelId);
                $module = null;
                $moduleNameKebab = null;

                // Try all possible module name prefixes (from longest to shortest)
                for ($i = count($parts) - 1; $i > 0; $i--) {
                    $possibleModuleNameKebab = implode('-', array_slice($parts, 0, $i));
                    // Convert kebab-case to StudlyCase for Module::find()
                    $possibleModuleNameStudly = str($possibleModuleNameKebab)->studly()->toString();
                    $foundModule = Module::find($possibleModuleNameStudly);
                    if ($foundModule) {
                        $module = $foundModule;
                        $moduleNameKebab = $possibleModuleNameKebab;
                        break;
                    }
                }

                if (! $module) {
                    return null;
                }

                // Skip disabled modules
                if (! $module->isEnabled()) {
                    return null;
                }

                // Extract panel label from the remaining parts after module name
                $panelLabelParts = array_slice($parts, count(explode('-', $moduleNameKebab)));
                $panelLabel = implode('-', $panelLabelParts);
                $label = $modulePanel->getBrandName() ?? str($panelLabel)->studly()->snake()->replace('_', ' ')->toString();

                return NavigationItem::make($label)
                    ->group($group)
                    ->sort($groupSort)
                    ->url($modulePanel->getUrl())
                    ->openUrlInNewTab($openInNewTab);
            })->filter()->toArray();

            $panel->navigationItems($navItems);
        }
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    protected function getModulePlugins(): array
    {
        if (! config('filament-modules.auto-register-plugins', false)) {
            return [];
        }
        // get a glob of all Filament plugins
        $basePath = str(config('modules.paths.modules', 'Modules'));
        $appFolder = trim(config('modules.paths.app_folder', 'app'), '/\\');
        $appPath = $appFolder . DIRECTORY_SEPARATOR;
        $pattern = str($basePath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $appPath . 'Filament' . DIRECTORY_SEPARATOR . '*Plugin.php')->replace('//', '/')->toString();
        $pluginPaths = glob($pattern);

        // Normalize paths to use consistent directory separators (fixes Windows mixed-slash issue)
        $pluginPaths = array_map(fn($path) => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), $pluginPaths);

        return collect($pluginPaths)
            ->map(fn ($path) => FilamentModules::convertPathToNamespace($path))
            ->filter(function ($class) {
                // Extract module name from plugin class namespace
                $moduleName = str($class)->after('Modules\\')->before('\\Filament\\')->toString();
                $module = Module::find($moduleName);
                
                // Only include plugins from enabled modules
                return $module && $module->isEnabled();
            })
            ->toArray();

    }

    /**
     * Get all Filament panels registered by modules.
     *
     * @return Panel[]
     */
    protected function getModulePanels(): array
    {
        // get a glob of all Filament panels
        $basePath = str(config('modules.paths.modules', 'Modules'));
        $appFolder = str(config('modules.paths.app_folder', 'app'));
        $pattern = $basePath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $appFolder . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . 'Filament' . DIRECTORY_SEPARATOR . '*.php';
        $panelPaths = glob($pattern);

        // Normalize paths to use consistent directory separators (fixes Windows mixed-slash issue)
        $panelPaths = array_map(fn($path) => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), $panelPaths);

        $panelIds = collect($panelPaths)->map(fn ($path) => FilamentModules::convertPathToNamespace($path))->map(function ($class) {
            // Get the panel ID and check if it is registered
            $id = str($class)->afterLast('\\')->before('PanelProvider')->kebab()->lower();
            // get module it belongs to as well
            $moduleName = str($class)->after('Modules\\')->before('\\Providers\\Filament');
            $module = Module::find($moduleName);
            if (! $module) {
                return null;
            }

            // Skip disabled modules
            if (! $module->isEnabled()) {
                return null;
            }

            return str($id)->prepend('-')->prepend($module->getKebabName());
        });

        return collect(filament()->getPanels())->filter(function ($panel) use ($panelIds) {
            // Check if the panel ID is in the list of panel IDs
            return $panelIds->contains($panel->getId());
        })->values()->all();

    }
}
