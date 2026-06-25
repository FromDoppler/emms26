<?php

class CheckoutExecutionRuntime
{
    private $correlationId;
    private $transactionId;
    private $idempotencyKey;
    private $dbTransactionOpen;
    private $providerApproved;
    private $approvedProviderResult;
    private $localCommitCompleted;

    private function __construct(string $correlationId)
    {
        $this->correlationId = $correlationId;
        $this->transactionId = null;
        $this->idempotencyKey = '';
        $this->dbTransactionOpen = false;
        $this->providerApproved = false;
        $this->approvedProviderResult = null;
        $this->localCommitCompleted = false;
    }

    public static function start(): self
    {
        return new self('corr_' . bin2hex(random_bytes(16)));
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }

    public function transactionId(): ?int
    {
        return $this->transactionId;
    }

    public function idempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function approvedProviderResult(): ?ProviderPaymentResult
    {
        return $this->approvedProviderResult;
    }

    public function setIdempotencyKey(string $idempotencyKey): void
    {
        $this->idempotencyKey = $idempotencyKey;
    }

    public function markTransactionCreated(int $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    public function markDbTransactionOpen(): void
    {
        $this->dbTransactionOpen = true;
    }

    public function markDbTransactionClosed(): void
    {
        $this->dbTransactionOpen = false;
    }

    public function markProviderApproved(ProviderPaymentResult $result): void
    {
        $this->providerApproved = true;
        $this->approvedProviderResult = $result;
    }

    public function markLocalCommitCompleted(): void
    {
        $this->localCommitCompleted = true;
    }

    public function hasOpenDbTransaction(): bool
    {
        return $this->dbTransactionOpen;
    }

    public function providerApprovedButLocalCommitIncomplete(): bool
    {
        return $this->providerApproved && $this->approvedProviderResult instanceof ProviderPaymentResult && !$this->localCommitCompleted;
    }
}
