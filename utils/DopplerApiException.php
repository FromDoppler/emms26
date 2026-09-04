<?php

class DopplerApiException extends RuntimeException
{
    private $httpStatus;
    private $errorCode;
    private $retryable;

    public function __construct(
        string $message,
        ?int $httpStatus = null,
        ?int $errorCode = null,
        bool $retryable = true
    ) {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->errorCode = $errorCode;
        $this->retryable = $retryable;
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }
}
