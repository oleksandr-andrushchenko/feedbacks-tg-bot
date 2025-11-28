<?php

declare(strict_types=1);

namespace OA\Dynamodb\Attribute;

abstract class AbstractKey
{
    public function __construct(
        /**
         * array<int, string>
         */
        public array $fields = [],
        public ?string $name = null,
        public ?string $delimiter = null,
        public ?string $prefix = null,
    )
    {
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }
}
