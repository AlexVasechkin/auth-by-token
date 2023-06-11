<?php

namespace Avn\Security\AuthByToken\Contracts;

interface HandleSuccefullTokenValidation
{
    public function handle(TokenDataInterface $tokenData, array $payload): void;
}
