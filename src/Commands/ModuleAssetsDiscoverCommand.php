<?php

namespace Coolsam\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Facades\Module;

class ModuleAssetsDiscoverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:assets:discover
                            {--update-vite : Update the main vite.config.js with discovered assets}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover and optionally register module assets in Vite configuration';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $updateVite = $this->option('update-vite');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Running in dry-run mode - no changes will be made');
        }

        $modules = Module::all();
        $filesystem = new Filesystem();

        $this->info('🔍 Discovering module assets...');

        $cssPaths = config('filament-modules.asset_discovery.css_paths', ['resources/css/**/*.css']);
        $jsPaths = config('filament-modules.asset_discovery.js_paths', ['resources/js/**/*.js']);

        $discoveredAssets = [
            'css' => [],
            'js' => [],
        ];

        foreach ($modules as $module) {
            $this->line("📦 Scanning module: {$module->getName()}");

            // Discover CSS files
            foreach ($cssPaths as $cssPattern) {
                $cssFiles = glob($module->getPath() . '/' . $cssPattern, GLOB_BRACE);
                foreach ($cssFiles as $cssFile) {
                    if (file_exists($cssFile)) {
                        $relativePath = str_replace(base_path() . '/', '', $cssFile);
                        $discoveredAssets['css'][] = $relativePath;
                        $this->line("  📄 Found CSS: {$relativePath}");
                    }
                }
            }

            // Discover JS files
            foreach ($jsPaths as $jsPattern) {
                $jsFiles = glob($module->getPath() . '/' . $jsPattern, GLOB_BRACE);
                foreach ($jsFiles as $jsFile) {
                    if (file_exists($jsFile)) {
                        $relativePath = str_replace(base_path() . '/', '', $jsFile);
                        $discoveredAssets['js'][] = $relativePath;
                        $this->line("  📄 Found JS: {$relativePath}");
                    }
                }
            }
        }

        $totalAssets = count($discoveredAssets['css']) + count($discoveredAssets['js']);
        $this->info("✅ Discovery complete! Found {$totalAssets} asset files");

        if ($updateVite && ! $dryRun) {
            $this->updateViteConfig($discoveredAssets);
        } elseif ($updateVite && $dryRun) {
            $this->info('🔍 Would update vite.config.js with the following assets:');
            $this->displayViteConfigChanges($discoveredAssets);
        } elseif (! $updateVite) {
            $this->info('💡 Use --update-vite to automatically update your vite.config.js');
        }
    }

    /**
     * Update the main vite.config.js file with discovered assets.
     */
    protected function updateViteConfig(array $assets): void
    {
        $viteConfigPath = base_path('vite.config.js');

        if (! file_exists($viteConfigPath)) {
            $this->error('❌ vite.config.js not found in project root');
            return;
        }

        $viteConfig = file_get_contents($viteConfigPath);
        $filesystem = new Filesystem();

        // Prepare asset entries for Vite config
        $cssEntries = array_map(fn($path) => "    \"{$path}\"", $assets['css']);
        $jsEntries = array_map(fn($path) => "    \"{$path}\"", $assets['js']);

        $allEntries = array_merge($cssEntries, $jsEntries);

        if (empty($allEntries)) {
            $this->warn('⚠️ No assets found to add to Vite config');
            return;
        }

        // Look for existing input array in vite config
        $inputPattern = '/input:\s*\[([^\]]*)\]/s';
        if (preg_match($inputPattern, $viteConfig, $matches)) {
            $existingInput = $matches[1];

            // Add module assets to the input array
            $newEntries = implode(",\n", $allEntries);
            $updatedInput = $existingInput . (empty(trim($existingInput)) ? '' : ',') . "\n" . $newEntries;

            $updatedViteConfig = preg_replace($inputPattern, "input: [\n{$updatedInput}\n]", $viteConfig);

            if ($dryRun = $this->option('dry-run')) {
                $this->info('🔍 Would update vite.config.js:');
                $this->line('--- Original input array ---');
                $this->line($matches[0]);
                $this->line('--- Updated input array ---');
                $this->line("input: [\n{$updatedInput}\n]");
            } else {
                $filesystem->put($viteConfigPath, $updatedViteConfig);
                $this->info('✅ Updated vite.config.js with module assets');
            }
        } else {
            $this->warn('⚠️ Could not find input array in vite.config.js. You may need to manually add the assets.');
            $this->displayViteConfigChanges($assets);
        }
    }

    /**
     * Display what changes would be made to vite.config.js.
     */
    protected function displayViteConfigChanges(array $assets): void
    {
        $this->line('Add the following to your vite.config.js input array:');

        foreach ($assets['css'] as $css) {
            $this->line("  \"{$css}\",");
        }

        foreach ($assets['js'] as $js) {
            $this->line("  \"{$js}\",");
        }
    }
}
