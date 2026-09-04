<?php

class UserEventJobException extends RuntimeException
{
    private $retryable;

    private function __construct(string $message, bool $retryable)
    {
        parent::__construct($message);
        $this->retryable = $retryable;
    }

    public static function retryable(string $message): self
    {
        return new self($message, true);
    }

    public static function terminal(string $message): self
    {
        return new self($message, false);
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }
}
