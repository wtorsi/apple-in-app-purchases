<?php declare(strict_types=1);

namespace Security\Token;

interface DecoderInterface
{
    public function decode(string $token): array;
}