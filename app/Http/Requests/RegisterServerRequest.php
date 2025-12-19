<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ServerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterServerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'server_id' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'ip'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'server_type' => ['required', Rule::enum(ServerType::class)],
            'max_players' => ['required', 'integer', 'min:1', 'max:1000'],
            'current_players' => ['sometimes', 'integer', 'min:0'],
            'map' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:64'],
            'ping' => ['nullable', 'integer', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
