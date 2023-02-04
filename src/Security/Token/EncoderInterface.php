<?php declare(strict_types=1);

namespace Security\Token;

interface EncoderInterface
{
    public function encode(array $data): string;
}