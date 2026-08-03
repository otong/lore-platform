<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchKnowledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:255'],
            'category_uuid' => ['nullable', 'string', 'uuid'],
            'tag' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
            'author_uuid' => ['nullable', 'string', 'max:255'],
            'created_from' => ['nullable', 'date_format:Y-m-d'],
            'created_until' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:created_from'],
            'sort' => ['nullable', 'string', 'in:relevance,created_at,updated_at,title,views_count'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
