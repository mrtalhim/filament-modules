<?php

namespace Coolsam\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Finder\Finder;

class ModuleNamespaceUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:namespace:update
                            {module : The name of the module}
                            {--from= : The namespace to replace (e.g., "App\\")}
                            {--to= : The new namespace (e.g., "Modules\\MyModule\\")}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update namespaces in a module from one pattern to another';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $moduleName = $this->argument('module');
        $fromNamespace = $this->option('from');
        $toNamespace = $this->option('to');

        if (! $fromNamespace || ! $toNamespace) {
            $this->error('Both --from and --to options are required.');
            return;
        }

        $module = Module::find($moduleName);
        if (! $module) {
            $this->error("Module '{$moduleName}' not found.");
            return;
        }

        $this->info("Updating namespaces in module '{$moduleName}'");
        $this->info("From: {$fromNamespace}");
        $this->info("To: {$toNamespace}");

        $modulePath = $module->getPath();
        $filesystem = new Filesystem();

        // Find all PHP files in the module
        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($modulePath)
            ->exclude(['vendor', 'node_modules']);

        $updatedFiles = 0;
        $totalReplacements = 0;

        foreach ($finder as $file) {
            $filePath = $file->getRealPath();
            $content = $filesystem->get($filePath);
            $originalContent = $content;

            // Replace namespace declarations
            $content = $this->replaceNamespaceDeclarations($content, $fromNamespace, $toNamespace);

            // Replace use statements
            $content = $this->replaceUseStatements($content, $fromNamespace, $toNamespace);

            // Replace class instantiations and static calls
            $content = $this->replaceClassReferences($content, $fromNamespace, $toNamespace);

            if ($content !== $originalContent) {
                $filesystem->put($filePath, $content);
                $updatedFiles++;

                // Count replacements in this file
                $replacementsInFile = substr_count($content, $toNamespace) - substr_count($originalContent, $toNamespace);
                $totalReplacements += $replacementsInFile;

                $this->line("Updated: " . str_replace($modulePath . DIRECTORY_SEPARATOR, '', $filePath));
            }
        }

        $this->info("✅ Namespace update completed!");
        $this->info("📁 Files updated: {$updatedFiles}");
        $this->info("🔄 Total replacements: {$totalReplacements}");

        if ($updatedFiles > 0) {
            $this->warn("⚠️  Please run 'composer dump-autoload' to refresh the autoloader.");
            $this->warn("⚠️  Test your application thoroughly after namespace changes.");
        }
    }

    /**
     * Replace namespace declarations.
     */
    protected function replaceNamespaceDeclarations(string $content, string $from, string $to): string
    {
        // Match namespace declarations
        $pattern = '/^namespace\s+' . preg_quote($from, '/') . '(.+)?;/m';
        return preg_replace($pattern, 'namespace ' . $to . '$1;', $content);
    }

    /**
     * Replace use statements.
     */
    protected function replaceUseStatements(string $content, string $from, string $to): string
    {
        // Match use statements
        $pattern = '/^use\s+' . preg_quote($from, '/') . '(.+);/m';
        return preg_replace($pattern, 'use ' . $to . '$1;', $content);
    }

    /**
     * Replace class references in code (instantiations, static calls, etc.).
     */
    protected function replaceClassReferences(string $content, string $from, string $to): string
    {
        // This is a more complex replacement that needs to be careful about word boundaries
        // We need to replace fully qualified class names in various contexts

        // Replace new ClassName() patterns
        $pattern = '/new\s+' . preg_quote($from, '/') . '([A-Za-z_][A-Za-z0-9_]*(?:\\[A-Za-z_][A-Za-z0-9_]*)*)/';
        $content = preg_replace($pattern, 'new ' . $to . '$1', $content);

        // Replace static calls like ClassName::method()
        $pattern = '/' . preg_quote($from, '/') . '([A-Za-z_][A-Za-z0-9_]*(?:\\[A-Za-z_][A-Za-z0-9_]*)*)::/';
        $content = preg_replace($pattern, $to . '$1::', $content);

        // Replace type hints and other fully qualified references
        $pattern = '/(?<![\w\\\\])' . preg_quote($from, '/') . '([A-Za-z_][A-Za-z0-9_]*(?:\\[A-Za-z_][A-Za-z0-9_]*)*)(?![\w\\\\])/';
        $content = preg_replace($pattern, $to . '$1', $content);

        return $content;
    }
}
