<?php
declare(strict_types=1);

namespace Apple\Enum;

enum AppleReceiptStatus: int
{
    case NO_ERROR = 0;
    case ERROR = 1;
    case INVALID_ENV = 2;
    case MAX_RETRY_REACHED = 3;
    case RETRYING = 4;
}