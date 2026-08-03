<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tags' => ['required', 'array'],
            'tags.*' => ['required', 'string', 'max:50'],
        ];
    }
}
