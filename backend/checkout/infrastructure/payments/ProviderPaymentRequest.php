<?php

class ProviderPaymentRequest
{
    public $publicId;
    public $correlationId;
    public $idempotencyKey;
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
        $this->publicId = $data['publicId'] ?? null;
        $this->correlationId = $data['correlationId'] ?? null;
        $this->idempotencyKey = $data['idempotencyKey'] ?? null;
        $this->checkoutTransactionId = $data['checkoutTransactionId'] ?? null;
        $this->customerId = $data['customerId'] ?? null;
        $this->finalAmount = $data['finalAmount'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->customerEmail = $data['customerEmail'] ?? null;
        $this->customerName = $data['customerName'] ?? null;
        $this->worldPayLowValueToken = $data['worldPayLowValueToken'] ?? null;
        $this->ccExpMonth = $data['ccExpMonth'] ?? null;
        $this->ccExpYear = $data['ccExpYear'] ?? null;
        $this->ccType = $data['ccType'] ?? null;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
