<?php

class DopplerApiException extends RuntimeException
{
    private $httpStatus;
    private $errorCode;
    private $detail;

    public function __construct(
        string $message,
        ?int $httpStatus = null,
        ?int $errorCode = null,
        ?string $detail = null
    ) {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->errorCode = $errorCode;
        $this->detail = $detail;
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }
}
