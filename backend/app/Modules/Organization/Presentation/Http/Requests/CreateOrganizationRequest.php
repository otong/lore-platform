<?php

declare(strict_types=1);

namespace App\Modules\Organization\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
