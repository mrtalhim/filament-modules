<?php

// config for Coolsam/Modules
return [
    'mode' => \Coolsam\Modules\Enums\ConfigMode::BOTH->value, // 'plugins' or 'panels', determines how the Filament Modules are registered
    'auto-register-plugins' => true, // whether to auto-register plugins from various modules in the Panel. Only relevant if 'mode' is set to 'plugins'.
    'auto_register_panels' => true, // whether to auto-register module panel providers
    'clusters' => [
        'enabled' => true, // whether to enable the clusters feature which allows you to group each module's filament resources and pages into a cluster
        'use-top-navigation' => true, // display the main cluster menu in the top navigation and the sub-navigation in the side menu, which improves the UI
    ],
    'panels' => [
        'group' => 'Panels', // the group name for the panels in the navigation
        'group-icon' => \Filament\Support\Icons\Heroicon::OutlinedRectangleStack,
        'group-sort' => 0, // the sort order of the panels group in the navigation
        'open-in-new-tab' => false, // whether to open the panels in a new tab
        'back_to_main_label' => 'Back to Admin', // label for the back to main panel navigation item
        'back_to_main_icon' => 'heroicon-o-arrow-left', // icon for the back to main panel navigation item
        'back_to_main_url' => '/admin', // URL for the back to main panel navigation item
        'require_auth' => true, // whether module panels should enforce authentication (shares main panel auth)
    ],
    'module_panel' => [
        'default_id' => 'admin', // the default ID for a module's Filament panel
        'path_strategy' => 'module_only', // how the URL path is constructed: 'module_prefix_with_id', 'module_only', 'panel_id_only'
        'auto_create_on_install' => true, // whether module:filament:install automatically creates a panel
        'skip_nwidart_defaults' => true, // whether to skip nwidart/laravel-modules default file generation
    ],
];
