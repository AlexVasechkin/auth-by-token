<?php

namespace Avn\Security\AuthByToken\Contracts;

interface HandleSuccefullTokenValidationInterface
{
    public function handle(TokenDataInterface $tokenData, array $payload): void;
}
