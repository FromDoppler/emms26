<?php

interface UserEventJobHandler
{
    public function jobType(): string;
    public function handle(array $job): void;
}

