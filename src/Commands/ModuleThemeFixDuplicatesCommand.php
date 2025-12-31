<?php

namespace Coolsam\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Facades\Module;

class ModuleThemeFixDuplicatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:theme:fix-duplicates
                            {--module= : Target specific module}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan theme files and provide actionable TODOs for fixing deduplication issues';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $targetModule = $this->option('module');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Running in dry-run mode - no changes will be made');
        }

        $modules = $targetModule
            ? [Module::find($targetModule)]
            : Module::all();

        $this->info('🔍 Scanning module theme files for deduplication issues...');

        $issues = [];
        $todos = [];

        foreach ($modules as $module) {
            if (!$module) {
                continue;
            }

            $this->line("📦 Scanning module: {$module->getName()}");
            $moduleIssues = $this->scanModuleThemeFiles($module);

            if (!empty($moduleIssues)) {
                $issues[$module->getName()] = $moduleIssues;
            }
        }

        if (empty($issues)) {
            $this->info('✅ No theme deduplication issues found!');
            return;
        }

        // Generate actionable TODOs
        $todos = $this->generateActionableTodos($issues);

        $this->displayIssuesAndTodos($issues, $todos);

        if (!$dryRun) {
            $this->offerToFixIssues($issues, $todos);
        }
    }

    protected function scanModuleThemeFiles(\Nwidart\Modules\Module $module): array
    {
        $issues = [];

        $themeDir = $module->resourcesPath('css/filament');
        if (!is_dir($themeDir)) {
            return $issues;
        }

        $themeFiles = glob($themeDir . '/theme*.css');
        foreach ($themeFiles as $themeFile) {
            $issues = array_merge($issues, $this->analyzeThemeFile($module, $themeFile));
        }

        return $issues;
    }

    protected function analyzeThemeFile(\Nwidart\Modules\Module $module, string $filePath): array
    {
        $issues = [];

        if (!file_exists($filePath)) {
            return $issues;
        }

        $content = file_get_contents($filePath);
        $fileName = basename($filePath);

        // Check if file has module and panel identifiers
        $hasModuleIdentifier = str_contains($content, '[data-module="' . $module->getName() . '"]');
        $hasPanelIdentifier = preg_match('/\[data-panel="([^"]+)"\]/', $content, $matches);

        if (!$hasModuleIdentifier) {
            $issues[] = [
                'type' => 'missing_module_identifier',
                'file' => $fileName,
                'message' => "Theme file missing module-specific identifier for {$module->getName()}",
                'file_path' => $filePath,
            ];
        }

        if (!$hasPanelIdentifier) {
            // Try to infer panel from filename
            $inferredPanel = $this->inferPanelFromFilename($fileName);
            $issues[] = [
                'type' => 'missing_panel_identifier',
                'file' => $fileName,
                'message' => "Theme file missing panel-specific identifier" . ($inferredPanel ? " (inferred: {$inferredPanel})" : ""),
                'file_path' => $filePath,
                'inferred_panel' => $inferredPanel,
            ];
        }

        // Check for duplicate selectors that could cause issues
        $duplicateSelectors = $this->findDuplicateSelectors($content);
        if (!empty($duplicateSelectors)) {
            $issues[] = [
                'type' => 'duplicate_selectors',
                'file' => $fileName,
                'message' => "Found duplicate selectors that may cause deduplication issues: " . implode(', ', $duplicateSelectors),
                'file_path' => $filePath,
                'duplicates' => $duplicateSelectors,
            ];
        }

        return $issues;
    }

    protected function inferPanelFromFilename(string $filename): ?string
    {
        // Try to extract panel from filename like theme-admin.css or theme-member.css
        if (preg_match('/theme-([a-zA-Z0-9_-]+)\.css$/', $filename, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function findDuplicateSelectors(string $content): array
    {
        $duplicates = [];

        // Simple check for common selectors that might be duplicated
        $commonSelectors = [
            '.fi-body',
            '.fi-sidebar',
            '.fi-topbar',
            '.fi-page',
        ];

        foreach ($commonSelectors as $selector) {
            $count = substr_count($content, $selector . ' {');
            $count += substr_count($content, $selector . '{');
            if ($count > 1) {
                $duplicates[] = $selector;
            }
        }

        return $duplicates;
    }

    protected function generateActionableTodos(array $issues): array
    {
        $todos = [];

        foreach ($issues as $moduleName => $moduleIssues) {
            foreach ($moduleIssues as $issue) {
                $todo = $this->createTodoFromIssue($moduleName, $issue);
                if ($todo) {
                    $todos[] = $todo;
                }
            }
        }

        return $todos;
    }

    protected function createTodoFromIssue(string $moduleName, array $issue): ?array
    {
        switch ($issue['type']) {
            case 'missing_module_identifier':
                return [
                    'priority' => 'high',
                    'module' => $moduleName,
                    'file' => $issue['file'],
                    'action' => 'Add module-specific CSS selector',
                    'command' => "Edit {$issue['file_path']} and add: .fi-theme-identifier[data-module=\"{$moduleName}\"][data-panel=\"<panel-id>\"] { /* styles */ }",
                    'description' => 'Add module-specific identifier to prevent theme conflicts',
                ];

            case 'missing_panel_identifier':
                $panelId = $issue['inferred_panel'] ?? 'admin';
                return [
                    'priority' => 'high',
                    'module' => $moduleName,
                    'file' => $issue['file'],
                    'action' => 'Add panel-specific CSS selector',
                    'command' => "Edit {$issue['file_path']} and add: .fi-theme-identifier[data-module=\"{$moduleName}\"][data-panel=\"{$panelId}\"] { /* styles */ }",
                    'description' => 'Add panel-specific identifier to prevent theme conflicts',
                ];

            case 'duplicate_selectors':
                return [
                    'priority' => 'medium',
                    'module' => $moduleName,
                    'file' => $issue['file'],
                    'action' => 'Review duplicate selectors',
                    'command' => "Review {$issue['file_path']} and consider namespacing selectors: " . implode(', ', $issue['duplicates']),
                    'description' => 'Duplicate selectors may cause unexpected styling behavior',
                ];

            default:
                return null;
        }
    }

    protected function displayIssuesAndTodos(array $issues, array $todos): void
    {
        $this->error("🚨 Found theme deduplication issues:");
        $this->line("");

        $todoIndex = 1;
        foreach ($todos as $todo) {
            $priorityIcon = match($todo['priority']) {
                'high' => '🔴',
                'medium' => '🟡',
                'low' => '🟢',
                default => '⚪',
            };

            $this->line("{$priorityIcon} TODO #{$todoIndex}: {$todo['action']}");
            $this->line("   Module: {$todo['module']}");
            $this->line("   File: {$todo['file']}");
            $this->line("   Description: {$todo['description']}");
            $this->line("   Command: {$todo['command']}");
            $this->line("");

            $todoIndex++;
        }

        $this->info("💡 Run this command again after fixing issues to verify resolution.");
    }

    protected function offerToFixIssues(array $issues, array $todos): void
    {
        if ($this->confirm('Would you like to attempt automatic fixes for some issues?', false)) {
            $this->attemptAutomaticFixes($issues);
        }
    }

    protected function attemptAutomaticFixes(array $issues): void
    {
        foreach ($issues as $moduleName => $moduleIssues) {
            $module = Module::find($moduleName);
            if (!$module) {
                continue;
            }

            foreach ($moduleIssues as $issue) {
                if ($issue['type'] === 'missing_module_identifier' || $issue['type'] === 'missing_panel_identifier') {
                    $this->fixMissingIdentifiers($module, $issue);
                }
            }
        }
    }

    protected function fixMissingIdentifiers(\Nwidart\Modules\Module $module, array $issue): void
    {
        $filePath = $issue['file_path'];
        if (!file_exists($filePath)) {
            return;
        }

        $content = file_get_contents($filePath);

        // Check if the identifier already exists
        $moduleIdentifier = "[data-module=\"{$module->getName()}\"]";
        if (str_contains($content, $moduleIdentifier)) {
            return;
        }

        $panelId = $issue['inferred_panel'] ?? 'admin';
        $panelIdentifier = "[data-panel=\"{$panelId}\"]";

        // Add the identifier selector
        $identifierSelector = ".fi-theme-identifier{$moduleIdentifier}{$panelIdentifier} {\n    /* Add module-specific styles here */\n}\n\n";

        // Insert after the Filament import but before @config
        $importPattern = '@import \'[^\']*filament[^\']*theme\.css\';';
        if (preg_match($importPattern, $content)) {
            $content = preg_replace(
                "/({$importPattern}\s*)/",
                "$1\n{$identifierSelector}",
                $content
            );
        } else {
            // Fallback: add at the beginning
            $content = $identifierSelector . $content;
        }

        file_put_contents($filePath, $content);
        $this->info("✅ Fixed identifiers in {$issue['file']}");
    }
}
