<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Knowledge\Application\Services\KnowledgeService;
use App\Modules\Knowledge\Presentation\Http\Requests\AddAttachmentMetadataRequest;
use App\Modules\Knowledge\Presentation\Http\Requests\SyncTagsRequest;
use App\Modules\Knowledge\Presentation\Http\Requests\UpdateKnowledgeRequest;
use App\Modules\Knowledge\Presentation\Http\Resources\AttachmentResource;
use App\Modules\Knowledge\Presentation\Http\Resources\KnowledgeResource;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class KnowledgeController extends Controller
{
    public function __construct(
        protected KnowledgeService $knowledgeService
    ) {}

    public function show(string $uuid): JsonResponse
    {
        $knowledge = $this->knowledgeService->getKnowledgeByUuid($uuid);

        if (! $knowledge) {
            return response()->json([
                'success' => false,
                'message' => "Knowledge article not found for UUID: {$uuid}",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Knowledge article details retrieved successfully',
            'data' => new KnowledgeResource($knowledge),
        ], 200);
    }

    public function update(UpdateKnowledgeRequest $request, string $uuid): JsonResponse
    {
        try {
            $knowledge = $this->knowledgeService->updateKnowledge($uuid, array_filter($request->validated(), fn ($v) => $v !== null));

            return response()->json([
                'success' => true,
                'message' => 'Knowledge article updated successfully',
                'data' => new KnowledgeResource($knowledge->load(['category', 'tags', 'attachments'])),
            ], 200);
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->knowledgeService->deleteKnowledge($uuid);

            return response()->json([
                'success' => true,
                'message' => 'Knowledge article deleted successfully',
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function publish(string $uuid): JsonResponse
    {
        try {
            $knowledge = $this->knowledgeService->publishKnowledge($uuid);

            return response()->json([
                'success' => true,
                'message' => 'Knowledge article published successfully',
                'data' => new KnowledgeResource($knowledge->load(['category', 'tags', 'attachments'])),
            ], 200);
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }
    }

    public function archive(string $uuid): JsonResponse
    {
        try {
            $knowledge = $this->knowledgeService->archiveKnowledge($uuid);

            return response()->json([
                'success' => true,
                'message' => 'Knowledge article archived successfully',
                'data' => new KnowledgeResource($knowledge->load(['category', 'tags', 'attachments'])),
            ], 200);
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }
    }

    public function syncTags(SyncTagsRequest $request, string $uuid): JsonResponse
    {
        try {
            $knowledge = $this->knowledgeService->syncTags($uuid, $request->validated('tags'));

            return response()->json([
                'success' => true,
                'message' => 'Tags synced successfully',
                'data' => new KnowledgeResource($knowledge->load(['category', 'tags', 'attachments'])),
            ], 200);
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }
    }

    public function addAttachment(AddAttachmentMetadataRequest $request, string $uuid): JsonResponse
    {
        try {
            $uploaderId = (int) $request->user()->id;
            $attachment = $this->knowledgeService->addAttachment($uuid, $uploaderId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Attachment metadata added successfully',
                'data' => new AttachmentResource($attachment),
            ], 201);
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }
    }
}
