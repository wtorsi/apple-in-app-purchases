<?php
declare(strict_types=1);

namespace Apple\Event;

use Apple\Enum\NotificationSubType;
use Apple\Event\Data\RenewalInfo;
use Apple\Event\Data\TransactionInfo;

abstract class AbstractEvent
{
    protected NotificationSubType|null $subType = null;
    protected RenewalInfo|null $renewalInfo = null;
    protected TransactionInfo|null $transactionInfo;

    public function __construct(protected readonly array $payload)
    {
        $this->subType = isset($payload['subtype']) ? NotificationSubType::tryFrom($payload['subtype']) : null;
        $this->renewalInfo = isset($payload['data']['signedRenewalInfo']) ? new RenewalInfo($payload['data']['signedRenewalInfo']) : null;
        $this->transactionInfo = isset($payload['data']['signedTransactionInfo']) ? new TransactionInfo($payload['data']['signedTransactionInfo']) : null;
    }

    public function getSubType(): NotificationSubType|null
    {
        return $this->subType;
    }

    public function getRenewalInfo(): RenewalInfo|null
    {
        return $this->renewalInfo;
    }

    public function getTransactionInfo(): TransactionInfo|null
    {
        return $this->transactionInfo;
    }
}