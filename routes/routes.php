<?php

Route::group(config('settings-api.routes.group'), function () {

    Route::get(
        config('settings-api.routes.url'),
        [
            config('settings-api.implementations.controller'),
            'read'
        ]
    )->name(config('settings-api.routes.names.read'));

    Route::patch(
        config('settings-api.routes.url'),
        [
            config('settings-api.implementations.controller'),
            'write'
        ]
    )->name(config('settings-api.routes.names.write'));

});
