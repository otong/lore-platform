<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKnowledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'category_uuid' => ['nullable', 'string', 'uuid'],
            'content' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
        ];
    }
}
