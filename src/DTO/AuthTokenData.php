<?php

namespace Avn\Security\AuthByToken\DTO;

use Avn\Security\AuthByToken\Contracts\TokenDataInterface;

class AuthTokenData
    implements TokenDataInterface
{
    private string $value;

    private string $hash;

    public function __construct(string $value, string $hash)
    {
        $this->value = $value;
        $this->hash = $hash;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
