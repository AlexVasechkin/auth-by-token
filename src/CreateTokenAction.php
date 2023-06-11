<?php

namespace Avn\Security\AuthByToken;

use DateInterval;
use Exception;
use Firebase\JWT\JWT;
use Psr\Cache\CacheItemPoolInterface;
use Faker;

class CreateTokenAction
{
    private CacheItemPoolInterface $cacheItemPool;

    private Faker\Generator $generator;

    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->generator = Faker\Factory::create('ru_RU');
    }

    /**
     * Return token lifetime in seconds
     */
    private function getTokenLifetime(): int
    {
        return 60 * 1;
    }

    private function getTokenFromCache(string $userIdentifier, string $hash): array
    {
        $item = $this->cacheItemPool->getItem(sprintf(($_ENV[ConstProvider::ENV_CACHE_PREFIX_KEY] ?? ConstProvider::DEFAULT_CACHE_PREFIX) . '%s', $userIdentifier));

        if (!$item->isHit()) {
            $item->set([
                'value' => $this->generator->password(20),
                'hash' => $hash
            ]);

            $item->expiresAfter($this->getTokenLifetime());

            $this->cacheItemPool->save($item);
        }

        return $item->get();
    }

    public function execute(string $userIdentifier): string
    {
        $now = new \DateTime();
        $hash = hash('sha256', sprintf('%s', $now->getTimestamp()));

        $key = $this->getTokenFromCache($userIdentifier, $hash);

        if ($hash !== $key['hash']) {
            throw new Exception('Auth token already exists');
        }

        $payload = [
            'sub' => $userIdentifier,
            'exp' => date_add($now, new DateInterval(sprintf('PT%dS', $this->getTokenLifetime())))->getTimestamp()
        ];

        return JWT::encode($payload, $key['value'], 'HS512');
    }
}
