<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGameInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'steam_app_id' => [
                'required',
                'integer',
                'min:1',
                'unique:game_instances,steam_app_id',
            ],
            'name' => [
                'required',
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
        ];
    }

    public function messages(): array
    {
        return [
            'steam_app_id.required' => 'Steam App ID is required.',
            'steam_app_id.integer' => 'Steam App ID must be a number.',
            'steam_app_id.min' => 'Steam App ID must be a positive number.',
            'steam_app_id.unique' => 'A game instance with this Steam App ID already exists.',
            'name.required' => 'Game name is required.',
            'name.min' => 'Game name must be at least 2 characters.',
            'memory_limit_mb.min' => 'Memory limit must be at least 1 MB.',
            'memory_limit_mb.max' => 'Memory limit cannot exceed 10 MB.',
        ];
    }
}
