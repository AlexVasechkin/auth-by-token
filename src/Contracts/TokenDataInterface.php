<?php

namespace Avn\Security\AuthByToken\Contracts;

interface TokenDataInterface
{
    public function getValue(): string;

    public function getHash(): string;
}
