<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Knowledge\Application\Services\AttachmentApplicationService;
use App\Modules\Knowledge\Presentation\Http\Requests\GetTemporaryLinkRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends Controller
{
    public function __construct(
        protected AttachmentApplicationService $attachmentService
    ) {}

    public function download(Request $request, string $uuid): Response
    {
        if ($request->has('signature') && ! $request->hasValidSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired signature',
            ], 401);
        }

        try {
            return $this->attachmentService->downloadAttachment($uuid, $request->user());
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function temporaryLink(GetTemporaryLinkRequest $request, string $uuid): JsonResponse
    {
        try {
            $expiresInMinutes = (int) $request->input('expires_in', 30);
            $data = $this->attachmentService->generateTemporaryLink($uuid, $request->user(), $expiresInMinutes);

            return response()->json([
                'success' => true,
                'message' => 'Temporary link generated successfully',
                'data' => $data,
            ], 200)->withHeaders([
                'Cache-Control' => 'no-store',
                'Pragma' => 'no-cache',
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        try {
            $this->attachmentService->deleteAttachment($uuid, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully',
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
