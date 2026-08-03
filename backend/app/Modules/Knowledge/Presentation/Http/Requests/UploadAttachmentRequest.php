<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = config('knowledge.attachments.max_file_size_kb', 20480);
        $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'zip', 'txt'];

        return [
            'file' => ['required', 'file', "max:{$maxKb}", 'mimes:'.implode(',', $allowedExts)],
        ];
    }
}
