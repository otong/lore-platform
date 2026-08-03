<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__.'/api/identity.php';
    require __DIR__.'/api/organization.php';
});
