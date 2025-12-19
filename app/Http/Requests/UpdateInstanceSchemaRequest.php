<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstanceSchemaRequest extends FormRequest
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
            'schema' => ['required', 'array'],
            'schema.*.name' => ['required', 'string', 'max:64'],
            'schema.*.type' => ['required', 'string', 'in:string,integer,float,boolean,array'],
            'schema.*.required' => ['sometimes', 'boolean'],
            'schema.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
