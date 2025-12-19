<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\Models\ApiKey;
use App\Repositories\Contracts\ApiKeyRepositoryInterface;
use App\Services\Contracts\ServerAuthenticationServiceInterface;
use Illuminate\Http\Request;

class ServerAuthenticationService implements ServerAuthenticationServiceInterface
{
    private const HEADER_API_KEY = 'X-API-Key';
    private const HEADER_TIMESTAMP = 'X-Timestamp';
    private const HEADER_SIGNATURE = 'X-Signature';

    public function __construct(
        private readonly ApiKeyRepositoryInterface $apiKeyRepository,
    ) {}

    public function validateRequest(Request $request): bool
    {
        $apiKey = $this->getApiKeyFromRequest($request);

        if (! $apiKey) {
            return false;
        }

        $timestamp = (int) $request->header(self::HEADER_TIMESTAMP, '0');

        if (! $this->isTimestampValid($timestamp)) {
            return false;
        }

        $signature = $request->header(self::HEADER_SIGNATURE, '');
        $expectedSignature = $this->generateSignature(
            $request->method(),
            $request->path(),
            $timestamp,
            $request->getContent(),
            $apiKey->secret
        );

        if (! $this->verifySignature($expectedSignature, $signature)) {
            return false;
        }

        $apiKey->markAsUsed();

        return true;
    }

    public function getApiKeyFromRequest(Request $request): ?ApiKey
    {
        $key = $request->header(self::HEADER_API_KEY);

        if (! $key) {
            return null;
        }

        return $this->apiKeyRepository->findActiveByKey($key);
    }

    public function generateSignature(string $method, string $path, int $timestamp, string $body, string $secret): string
    {
        $data = strtoupper($method) . "\n" . $path . "\n" . $timestamp . "\n" . $body;

        return hash_hmac(
            config('master_server.server_auth.algorithm', 'sha256'),
            $data,
            $secret
        );
    }

    public function verifySignature(string $expectedSignature, string $actualSignature): bool
    {
        return hash_equals($expectedSignature, $actualSignature);
    }

    public function isTimestampValid(int $timestamp): bool
    {
        $tolerance = config('master_server.server_auth.timestamp_tolerance', 300);
        $now = time();

        return abs($now - $timestamp) <= $tolerance;
    }
}
