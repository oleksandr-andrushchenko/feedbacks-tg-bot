<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Uid\Uuid;

class IdGenerator
{
    public function generateId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function generateUuid(): string
    {
        return Uuid::v4()->toString();
    }
}