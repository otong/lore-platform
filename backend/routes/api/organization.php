<?php

use App\Modules\Organization\Presentation\Http\Controllers\DepartmentController;
use App\Modules\Organization\Presentation\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('api.v1.organizations.store');
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('api.v1.organizations.index');
    Route::get('/organizations/{uuid}', [OrganizationController::class, 'show'])->name('api.v1.organizations.show');
    Route::post('/organizations/{uuid}/departments', [DepartmentController::class, 'store'])->name('api.v1.organizations.departments.store');
    Route::post('/organizations/{uuid}/memberships', [OrganizationController::class, 'assignMember'])->name('api.v1.organizations.memberships.store');
});
