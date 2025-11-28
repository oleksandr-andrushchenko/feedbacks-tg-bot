<?php

declare(strict_types=1);

namespace OA\Dynamodb\Attribute;

use Attribute as PHPAttribute;

#[PHPAttribute(PHPAttribute::TARGET_PROPERTY)]
class Attribute
{
    public function __construct(
        public ?string $name = null,
        public bool $ignoreIfNull = true,
    )
    {
    }
}
