<?php
declare(strict_types=1);

namespace Apple\Enum;

enum NotificationSubType: string
{
    case UPGRADE = 'UPGRADE';
    case DOWNGRADE = 'DOWNGRADE';
    case AUTO_RENEW_ENABLED = 'AUTO_RENEW_ENABLED';
    case AUTO_RENEW_DISABLED = 'AUTO_RENEW_DISABLED';
    case GRACE_PERIOD = 'GRACE_PERIOD';
    case BILLING_RECOVERY = 'BILLING_RECOVERY';
    case VOLUNTARY = 'VOLUNTARY';
    case BILLING_RETRY = 'BILLING_RETRY';
    case PRICE_INCREASE = 'PRICE_INCREASE';
    case PRODUCT_NOT_FOR_SALE = 'PRODUCT_NOT_FOR_SALE';
    case INITIAL_BUY = 'INITIAL_BUY';
    case RESUBSCRIBE = 'RESUBSCRIBE';
    case PENDING = 'PENDING';
    case ACCEPTED = 'ACCEPTED';
}