<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instance extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'name',
        'schema',
        'description',
        'is_active',
    ];

    protected $casts = [
        'schema' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the game that owns this instance.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Scope to only active instances.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Validate data against the instance schema.
     */
    public function validateData(array $data): array
    {
        $errors = [];
        $schema = $this->schema ?? [];

        foreach ($schema as $field) {
            $fieldName = $field['name'] ?? null;
            $required = $field['required'] ?? false;
            $type = $field['type'] ?? 'string';

            if ($required && ! isset($data[$fieldName])) {
                $errors[$fieldName] = "The {$fieldName} field is required.";
                continue;
            }

            if (isset($data[$fieldName])) {
                $value = $data[$fieldName];
                $valid = match ($type) {
                    'integer' => is_int($value) || (is_string($value) && ctype_digit($value)),
                    'float' => is_numeric($value),
                    'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true),
                    'string' => is_string($value),
                    'array' => is_array($value),
                    default => true,
                };

                if (! $valid) {
                    $errors[$fieldName] = "The {$fieldName} field must be of type {$type}.";
                }
            }
        }

        return $errors;
    }
}
