<?php

namespace Coolsam\Modules\Commands\FileGenerators;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Coolsam\Modules\Http\Middleware\ModulePanelAuthMiddleware;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Commands\FileGenerators\ClassGenerator;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nwidart\Modules\Facades\Module as ModuleFacade;

class ModulePanelProviderClassGenerator extends ClassGenerator
{
    public ?\Nwidart\Modules\Module $module;

    final public function __construct(
        protected string $fqn,
        protected string $id,
        protected string $moduleName,
        protected string $navigationLabel,
        protected bool $isDefault = false,
        protected bool $autoRegister = true,
    ) {
        $this->module = ModuleFacade::find($this->moduleName);
        if (! $this->module) {
            throw new \InvalidArgumentException("Module '{$this->moduleName}' not found.");
        }
    }

    public function getNamespace(): string
    {
        return $this->extractNamespace($this->getFqn());
    }

    /**
     * @return array<string>
     */
    public function getImports(): array
    {
        return [
            Panel::class,
            $this->getExtends(),
            Color::class,
            Dashboard::class,
            FilamentInfoWidget::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];
    }

    public function getBasename(): string
    {
        return class_basename($this->getFqn());
    }

    public function getExtends(): string
    {
        return PanelProvider::class;
    }

    protected function addMethodsToClass(ClassType $class): void
    {
        $this->addAutoRegisterPropertyToClass($class);
        $this->addPanelMethodToClass($class);
        $this->addNavigationLabelMethodToClass($class);
    }

    public function getModule(): \Nwidart\Modules\Module
    {
        return $this->module;
    }

    protected function addPanelMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('panel')
            ->setPublic()
            ->setReturnType(Panel::class)
            ->setBody($this->generatePanelMethodBody());
        $method->addParameter('panel')
            ->setType(Panel::class);

        $this->configurePanelMethod($method);
    }

    protected function addNavigationLabelMethodToClass(ClassType $class): void
    {
        $class->addMethod('getNavigationLabel')
            ->setPublic()
            ->setReturnType('string')
            ->setBody($this->generateNavigationLabelMethodBody());
    }

    protected function generateNavigationLabelMethodBody(): string
    {
        $navigationLabel = $this->navigationLabel;

        return new Literal(
            <<<PHP
                return __("$navigationLabel");
            PHP,
        );
    }

    public function generatePanelMethodBody(): string
    {
        $isDefault = $this->isDefault();

        $defaultOutput = $isDefault
            ? <<<'PHP'

                    ->default()
                PHP
            : '';

        $loginOutput = <<<'PHP'

                    ->login(false)
                PHP;

        $id = str($this->getId())->kebab()->lower()->toString();
        $moduleKebabName = $this->getModule()->getKebabName();

        // Ensure unique panel ID by combining module name and panel ID
        $panelId = str($moduleKebabName)->append('-')->append($id)->toString();

        // Determine URL path based on configuration strategy
        $pathStrategy = config('filament-modules.module_panel.path_strategy', 'module_only');
        switch ($pathStrategy) {
            case 'module_prefix_with_id':
                $urlPath = str($moduleKebabName)->append('/')->append($id)->toString();

                break;
            case 'panel_id_only':
                $urlPath = $id;

                break;
            case 'module_only':
            default:
                $urlPath = $moduleKebabName;

                break;
        }

        $label = $this->getModule()->getTitle() . ' ' . str($id)->studly()->snake()->title()->replace(['_', '-'], ' ')->toString();
        $componentsDirectory = Str::studly($panelId);
        $componentsNamespace = (Str::studly($panelId) . '\\');

        $rootNamespace = str($this->getModule()->namespace())->rtrim('\\')->append('\\')->toString();
        $moduleName = $this->getModule()->getName();

        return new Literal(
            <<<PHP
                \$separator = DIRECTORY_SEPARATOR;
                return \$panel{$defaultOutput}
                    ->id(?)
                    ->path(?){$loginOutput}
                    ->brandName(\$this->getNavigationLabel())
                    ->colors([
                        'primary' => {$this->simplifyFqn(Color::class)}::Amber,
                    ])
                    ->discoverResources(in: module("$moduleName", true)->appPath("Filament{\$separator}{$componentsDirectory}{\$separator}Resources"), for: module("$moduleName", true)->appNamespace('Filament\\{$componentsNamespace}Resources'))
                    ->discoverPages(in:module("$moduleName", true)->appPath("Filament{\$separator}{$componentsDirectory}{\$separator}Pages"), for: module("$moduleName", true)->appNamespace('Filament\\{$componentsNamespace}Pages'))
                    ->pages([
                        {$this->simplifyFqn(Dashboard::class)}::class,
                    ])
                    ->discoverWidgets(in:module("$moduleName", true)->appPath("Filament{\$separator}{$componentsDirectory}{\$separator}Widgets"), for: module("$moduleName", true)->appNamespace('Filament\\{$componentsNamespace}Widgets'))
                    ->widgets([
                        {$this->simplifyFqn(FilamentInfoWidget::class)}::class,
                    ])
                    ->discoverClusters(in: module("$moduleName", true)->appPath("Filament{\$separator}{$componentsDirectory}{\$separator}Clusters"), for: module("$moduleName", true)->appNamespace('Filament\\{$componentsNamespace}Clusters'))
                    ->navigationItems([
                        \Filament\Navigation\NavigationItem::make('back-to-admin')
                            ->label(config('filament-modules.panels.back_to_main_label', 'Back to Admin'))
                            ->icon(config('filament-modules.panels.back_to_main_icon', 'heroicon-o-arrow-left'))
                            ->url(config('filament-modules.panels.back_to_main_url', '/admin'))
                            ->sort(-100),
                    ])
                    ->middleware([
                        {$this->simplifyFqn(EncryptCookies::class)}::class,
                        {$this->simplifyFqn(AddQueuedCookiesToResponse::class)}::class,
                        {$this->simplifyFqn(StartSession::class)}::class,
                        {$this->simplifyFqn(ShareErrorsFromSession::class)}::class,
                        {$this->simplifyFqn(VerifyCsrfToken::class)}::class,
                        {$this->simplifyFqn(SubstituteBindings::class)}::class,
                        {$this->simplifyFqn(DisableBladeIconComponents::class)}::class,
                        {$this->simplifyFqn(DispatchServingFilamentEvent::class)}::class,
                        {$this->simplifyFqn(ModulePanelAuthMiddleware::class)}::class,
                    ])
                    ->authMiddleware([]);
                PHP,
            [$panelId, $urlPath],
        );
    }

    protected function configurePanelMethod(Method $method): void {}

    public function getFqn(): string
    {
        return $this->fqn;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    protected function addAutoRegisterPropertyToClass(ClassType $class): void
    {
        $class->addProperty('autoRegister', $this->autoRegister)
            ->setPublic()
            ->setStatic()
            ->setType('bool');
    }
}
