<?php declare(strict_types=1);

namespace Apple\Exception\Token;

use Apple\Exception\ExceptionInterface;

class DecoderException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}