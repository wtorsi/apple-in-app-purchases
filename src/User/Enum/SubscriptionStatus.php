<?php
declare(strict_types=1);

namespace User\Enum;

use User\Exception\TransitionFailedException;

enum SubscriptionStatus: int
{
    case INACTIVE = 0;
    case PENDING = 1;
    case ACTIVE = 2;
    case APPLE_PENDING = 3;
    case APPLE_PENDING_VERIFIED = 4;
    case APPLE_PENDING_FAILED = 5;

    public static function activeCases(): array
    {
        return [
            self::ACTIVE,
            self::PENDING,
            self::APPLE_PENDING,
            self::APPLE_PENDING_VERIFIED,
        ];
    }

    public static function transition(self $from, self $to): self
    {
        $all = [...self::cases()];

        $allowed = match ($to) {
            self::APPLE_PENDING => [
                self::INACTIVE, // usual case to start processing the subscription
                self::APPLE_PENDING_FAILED, // the receipt was processed and failed - allow user to try again
                self::APPLE_PENDING, // todo clear status when expired
            ],
            self::APPLE_PENDING_VERIFIED, self::APPLE_PENDING_FAILED => [
                self::APPLE_PENDING, // it blocks the second processing of the receipt before previous one was processed
            ],
            self::ACTIVE => [
                self::APPLE_PENDING, // notification came before the receipt was processed
                self::APPLE_PENDING_VERIFIED, // event came after receipt was processed and verified
                self::APPLE_PENDING_FAILED, // event came after receipt was processed and failed
                self::ACTIVE, // prolong
                self::PENDING, // usually come from pending
            ],
            self::PENDING => [self::INACTIVE, self::PENDING],
            self::INACTIVE => $all,
            default => [],
        };

        if (!\in_array($from, $allowed)) {
            throw TransitionFailedException::create($from, $to, $allowed);
        }

        return $to;
    }
}