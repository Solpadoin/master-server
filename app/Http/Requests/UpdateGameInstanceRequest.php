<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:255',
            ],
            'memory_limit_mb' => [
                'sometimes',
                'integer',
                'min:1',
                'max:10',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
