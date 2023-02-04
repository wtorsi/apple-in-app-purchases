<?php

namespace Api\Apple\Form\Data;

class ProcessReceiptDto
{
    public string|null $transactionId = null;
    public string|null $productId = null;
    public string|null $receiptData = null;
}