<?php

namespace Avn\Security\AuthByToken;

use Avn\Security\AuthByToken\Contracts\HandleSuccefullTokenValidationInterface;
use Avn\Security\AuthByToken\DTO\AuthTokenData;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Firebase\JWT;

class ValidateTokenAction
{
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function execute(string $token, HandleSuccefullTokenValidationInterface $handleSuccess): void
    {
        $getPayload = function (string $token) {
            [, $payloadPart] = explode('.', $token);
            return json_decode(base64_decode($payloadPart), true);
        };

        $payload = $getPayload($token);

        if (!is_string($payload['sub'] ?? null)) {
            throw new Exception('sub: expected string.');
        }

        $cacheItem = $this->cacheItemPool->getItem(sprintf(($_ENV[ConstProvider::ENV_CACHE_PREFIX_KEY] ?? ConstProvider::DEFAULT_CACHE_PREFIX) . '%s', $payload['sub']));

        if (!$cacheItem->isHit()) {
            throw new Exception('Token not found!');
        }

        $tokenData = $cacheItem->get();

        if (!is_array($tokenData)) {
            throw new Exception('Token secret key not found');
        }

        JWT\JWT::decode($token, new JWT\Key($tokenData['value'], 'HS512'));

        $handleSuccess->handle(new AuthTokenData($tokenData['value'] ?? null, $tokenData['hash'] ?? null), $payload);

        $this->cacheItemPool->deleteItem(sprintf(($_ENV[ConstProvider::ENV_CACHE_PREFIX_KEY] ?? ConstProvider::DEFAULT_CACHE_PREFIX) . '%s', $payload['sub']));
    }
}
