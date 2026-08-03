<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateKnowledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'category_uuid' => ['nullable', 'string', 'uuid'],
            'content' => ['required', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
        ];
    }
}
