<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\ApiKey;
use Illuminate\Http\Request;

interface ServerAuthenticationServiceInterface
{
    /**
     * Validate the server authentication from request.
     */
    public function validateRequest(Request $request): bool;

    /**
     * Get the API key from request.
     */
    public function getApiKeyFromRequest(Request $request): ?ApiKey;

    /**
     * Generate HMAC signature for given data.
     */
    public function generateSignature(string $method, string $path, int $timestamp, string $body, string $secret): string;

    /**
     * Verify HMAC signature.
     */
    public function verifySignature(string $expectedSignature, string $actualSignature): bool;

    /**
     * Check if timestamp is within acceptable range.
     */
    public function isTimestampValid(int $timestamp): bool;
}
