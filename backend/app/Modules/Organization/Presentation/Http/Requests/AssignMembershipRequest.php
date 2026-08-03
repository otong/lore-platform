<?php

declare(strict_types=1);

namespace App\Modules\Organization\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'role' => ['nullable', 'string', 'in:owner,admin,member'],
            'status' => ['nullable', 'string', 'in:active,invited,suspended'],
        ];
    }
}
