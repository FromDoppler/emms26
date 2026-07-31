<?php

class ProviderPaymentRequest
{
    public $paymentId;
    public $correlationId;
    public $checkoutTransactionId;
    public $customerId;
    public $finalAmount;
    public $currency;
    public $customerEmail;
    public $customerName;
    public $worldPayLowValueToken;
    public $ccExpMonth;
    public $ccExpYear;
    public $ccType;

    public function __construct(array $data)
    {
        $this->paymentId = $data['paymentId'] ?? null;
        $this->correlationId = $data['correlationId'] ?? null;
        $this->checkoutTransactionId = $data['checkoutTransactionId'] ?? null;
        $this->customerId = $data['customerId'] ?? null;
        $this->finalAmount = $data['finalAmount'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->customerEmail = $data['customerEmail'] ?? null;
        $this->customerName = $data['customerName'] ?? null;
        $this->worldPayLowValueToken = $data['worldPayLowValueToken'] ?? null;
        $this->ccExpMonth = $data['ccExpMonth'] ?? null;
        $rawYear = trim((string) ($data['ccExpYear'] ?? ''));
        $this->ccExpYear = (strlen($rawYear) === 2 && ctype_digit($rawYear))
            ? 2000 + (int) $rawYear
            : (int) $rawYear;
        $this->ccType = $data['ccType'] ?? null;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
