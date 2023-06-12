<?php

namespace Avn\Security\AuthByToken\DTO;

class AuthTokenData
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
