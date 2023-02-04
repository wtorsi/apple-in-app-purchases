<?php
declare(strict_types=1);

namespace Apple\Enum;

enum ReceiptStatus: int
{
    case VALID = 0;
    case ERROR = 1;
    case RUNTIME_ERROR = 2;
    case INTERNAL_ERROR = 3;
    case TEMPORARY_ERROR = 4;
    case EXPIRED = 5;
    case FROM_SANDBOX_ENV = 6;

    public static function create(int $status): self
    {
        return match ($status) {
            0 => self::VALID,
            21002, 21005, 21009 => self::TEMPORARY_ERROR,
            21006 => self::EXPIRED,
            21007 => self::FROM_SANDBOX_ENV,
            21000, 21001, 21004 => self::RUNTIME_ERROR,
            21003, 21008, 21010 => self::ERROR,
            default => self::INTERNAL_ERROR,
        };
    }

    public function isRuntimeError(): bool
    {
        return self::RUNTIME_ERROR === $this;
    }

    public function isSandboxEnv(): bool
    {
        return self::FROM_SANDBOX_ENV === $this;
    }

    public function isInternal(): bool
    {
        return self::INTERNAL_ERROR === $this;
    }

    public function isTemporary(): bool
    {
        return self::TEMPORARY_ERROR === $this;
    }

    public function isValid(): bool
    {
        return self::VALID === $this || self::EXPIRED === $this;
    }
}