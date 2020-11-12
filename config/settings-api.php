<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configure Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'enabled' => true,
        'url' => 'settings',
        'names' => [
            'read' => 'settings.read',
            'write' => 'settings.write',
        ],
        'group' => [
            'middleware' => 'web',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bind Implementations
    |--------------------------------------------------------------------------
    */
    'implementations' => [

        // Controller
        'controller' => BonsaiCms\SettingsApi\Http\Controllers\SettingsController::class,

        // Requests
        BonsaiCms\SettingsApi\Contracts\ReadSettingsRequestContract::class => BonsaiCms\SettingsApi\Http\Requests\ReadSettingsRequest::class,
        BonsaiCms\SettingsApi\Contracts\WriteSettingsRequestContract::class => BonsaiCms\SettingsApi\Http\Requests\WriteSettingsRequest::class,

        // Responses
        BonsaiCms\SettingsApi\Contracts\ReadSettingsResponseContract::class => BonsaiCms\SettingsApi\Http\Responses\ReadSettingsResponse::class,
        BonsaiCms\SettingsApi\Contracts\WriteSettingsResponseContract::class => BonsaiCms\SettingsApi\Http\Responses\WriteSettingsResponse::class,

    ],

];
