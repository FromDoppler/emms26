<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/interfaces/UserEventJobHandler.php');

class UserEventJobHandlerRegistry
{
    private $handlers = [];

    public function __construct(array $handlers)
    {
        foreach ($handlers as $handler) {
            if (!$handler instanceof UserEventJobHandler) {
                throw new InvalidArgumentException('Invalid user event job handler registered');
            }

            $jobType = $handler->jobType();
            if (isset($this->handlers[$jobType])) {
                throw new Exception('Duplicate handler registered for job type ' . $jobType);
            }
            $this->handlers[$jobType] = $handler;
        }
    }

    public function get(string $jobType): UserEventJobHandler
    {
        if (!isset($this->handlers[$jobType])) {
            throw new Exception('No handler registered for job type ' . $jobType);
        }

        return $this->handlers[$jobType];
    }
}
