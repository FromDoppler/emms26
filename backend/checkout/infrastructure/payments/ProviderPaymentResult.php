<?php

class ProviderPaymentResult
{
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const ERROR = 'error';

    public $status;
    public $provider;
    public $providerTransactionId;
    public $authorizationNumber;
    public $transactionLinkId;
    public $authorizationResponseCode;
    public $purchaseResponseCode;
    public $responseCode;
    public $responseMessage;
    public $rawResponse;

    public function __construct(array $data)
    {
        $this->status = $data['status'];
        $this->provider = $data['provider'] ?? 'unknown';
        $this->providerTransactionId = $data['providerTransactionId'] ?? null;
        $this->authorizationNumber = $data['authorizationNumber'] ?? null;
        $this->transactionLinkId = $data['transactionLinkId'] ?? null;
        $this->authorizationResponseCode = $data['authorizationResponseCode'] ?? null;
        $this->purchaseResponseCode = $data['purchaseResponseCode'] ?? null;
        $this->responseCode = $data['responseCode'] ?? null;
        $this->responseMessage = $data['responseMessage'] ?? null;
        $this->rawResponse = $data['rawResponse'] ?? [];
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
