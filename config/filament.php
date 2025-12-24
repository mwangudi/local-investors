<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default panel
    |--------------------------------------------------------------------------
    |
    | The ID of the default Filament panel. The installer normally sets this.
    |
    */
    'default_panel' => env('FILAMENT_DEFAULT_PANEL', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Panels
    |--------------------------------------------------------------------------
    |
    | Define at least one panel. The 'admin' below is a simple default.
    |
    */
    'panels' => [
        'admin' => [
            'id' => 'admin',
            'path' => env('FILAMENT_PATH', 'admin'),
            'resources' => [
                // resources are discovered in app/Filament/Resources by default
            ],
            'widgets' => [
                //
            ],
            // Other keys can stay default/empty — generator only needs a panel entry
        ],
    ],

    // keep other defaults minimal to avoid missing keys
    'navigation' => [],
];