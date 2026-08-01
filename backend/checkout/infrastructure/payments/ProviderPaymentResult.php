<?php

class ProviderPaymentResult
{
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const ERROR = 'error';
    const UNKNOWN = 'unknown';

    public $status;
    public $provider;
    public $authorizationNumber;
    public $transactionLinkId;
    public $authorizationResponseCode;
    public $purchaseResponseCode;
    public $responseCode;

    public function __construct(array $data)
    {
        $this->status = $data['status'];
        $this->provider = $data['provider'] ?? 'unknown';
        $this->authorizationNumber = $data['authorizationNumber'] ?? null;
        $this->transactionLinkId = $data['transactionLinkId'] ?? null;
        $this->authorizationResponseCode = $data['authorizationResponseCode'] ?? null;
        $this->purchaseResponseCode = $data['purchaseResponseCode'] ?? null;
        $this->responseCode = $data['responseCode'] ?? null;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
