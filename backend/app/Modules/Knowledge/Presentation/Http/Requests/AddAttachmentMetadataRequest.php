<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddAttachmentMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_name' => ['required', 'string', 'max:255'],
            'disk' => ['nullable', 'string', 'max:50'],
            'path' => ['required', 'string', 'max:500'],
            'checksum' => ['nullable', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:100'],
            'file_size' => ['required', 'integer', 'min:1'],
        ];
    }
}
