<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Knowledge\Application\Services\AttachmentApplicationService;
use App\Modules\Knowledge\Presentation\Http\Requests\UploadAttachmentRequest;
use App\Modules\Knowledge\Presentation\Http\Resources\AttachmentResource;
use Illuminate\Http\JsonResponse;
use Throwable;

class KnowledgeAttachmentController extends Controller
{
    public function __construct(
        protected AttachmentApplicationService $attachmentService
    ) {}

    public function upload(UploadAttachmentRequest $request, string $uuid): JsonResponse
    {
        try {
            $uploaderId = (int) $request->user()->id;
            $file = $request->file('file');

            $attachment = $this->attachmentService->uploadAttachment($uuid, $uploaderId, $file);

            return response()->json([
                'success' => true,
                'message' => 'Attachment uploaded successfully',
                'data' => new AttachmentResource($attachment),
            ], 201);
        } catch (Throwable $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }
    }
}
