<?php

use App\Modules\Knowledge\Presentation\Http\Controllers\AttachmentController;
use App\Modules\Knowledge\Presentation\Http\Controllers\CategoryController;
use App\Modules\Knowledge\Presentation\Http\Controllers\KnowledgeAttachmentController;
use App\Modules\Knowledge\Presentation\Http\Controllers\KnowledgeController;
use App\Modules\Knowledge\Presentation\Http\Controllers\OrganizationCategoryController;
use App\Modules\Knowledge\Presentation\Http\Controllers\OrganizationKnowledgeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Category Endpoints
    Route::get('/organizations/{orgUuid}/categories', [OrganizationCategoryController::class, 'index'])->name('api.v1.knowledge.categories.index');
    Route::post('/organizations/{orgUuid}/categories', [OrganizationCategoryController::class, 'store'])->name('api.v1.knowledge.categories.store');
    Route::get('/categories/{uuid}', [CategoryController::class, 'show'])->name('api.v1.knowledge.categories.show');
    Route::patch('/categories/{uuid}', [CategoryController::class, 'update'])->name('api.v1.knowledge.categories.update');
    Route::delete('/categories/{uuid}', [CategoryController::class, 'destroy'])->name('api.v1.knowledge.categories.destroy');

    // Knowledge Article Endpoints
    Route::get('/organizations/{orgUuid}/knowledges', [OrganizationKnowledgeController::class, 'index'])->name('api.v1.knowledge.articles.index');
    Route::post('/organizations/{orgUuid}/knowledges', [OrganizationKnowledgeController::class, 'store'])->name('api.v1.knowledge.articles.store');
    Route::get('/knowledges/{uuid}', [KnowledgeController::class, 'show'])->name('api.v1.knowledge.articles.show');
    Route::patch('/knowledges/{uuid}', [KnowledgeController::class, 'update'])->name('api.v1.knowledge.articles.update');
    Route::delete('/knowledges/{uuid}', [KnowledgeController::class, 'destroy'])->name('api.v1.knowledge.articles.destroy');
    Route::post('/knowledges/{uuid}/publish', [KnowledgeController::class, 'publish'])->name('api.v1.knowledge.articles.publish');
    Route::post('/knowledges/{uuid}/archive', [KnowledgeController::class, 'archive'])->name('api.v1.knowledge.articles.archive');
    Route::post('/knowledges/{uuid}/tags', [KnowledgeController::class, 'syncTags'])->name('api.v1.knowledge.articles.tags.sync');
    Route::post('/knowledges/{uuid}/attachments', [KnowledgeController::class, 'addAttachment'])->name('api.v1.knowledge.articles.attachments.store');

    // Attachment Subsystem Endpoints (Sprint-009)
    Route::post('/knowledges/{uuid}/attachments/upload', [KnowledgeAttachmentController::class, 'upload'])->name('api.v1.knowledge.articles.attachments.upload');
    Route::get('/attachments/{uuid}/download', [AttachmentController::class, 'download'])->name('api.v1.knowledge.attachments.download');
    Route::post('/attachments/{uuid}/temporary-link', [AttachmentController::class, 'temporaryLink'])->name('api.v1.knowledge.attachments.temporary_link');
    Route::delete('/attachments/{uuid}', [AttachmentController::class, 'destroy'])->name('api.v1.knowledge.attachments.destroy');
});
