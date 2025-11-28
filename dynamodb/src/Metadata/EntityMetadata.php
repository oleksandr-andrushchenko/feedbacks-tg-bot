<?php

declare(strict_types=1);

namespace OA\Dynamodb\Metadata;

use OA\Dynamodb\Attribute\AbstractIndex;
use OA\Dynamodb\Attribute\AbstractKey;
use OA\Dynamodb\Attribute\Attribute;
use OA\Dynamodb\Attribute\Entity;

readonly class EntityMetadata
{
    public function __construct(
        protected Entity $entityAttribute,
        /**
         * @var array<string, Attribute>
         */
        protected array $propertyAttributes,
        protected array $defaults = [],
    )
    {
    }

    public function getTable(): ?string
    {
        return $this->entityAttribute->table;
    }

    public function getPartitionKey(): AbstractKey
    {
        return $this->entityAttribute->partitionKey;
    }

    public function getSortKey(): ?AbstractKey
    {
        return $this->entityAttribute->sortKey;
    }

    /**
     * @return array<int, AbstractIndex>
     */
    public function getIndexes(): array
    {
        return $this->entityAttribute->indexes;
    }

    /**
     * @return array<string, Attribute>
     */
    public function getPropertyAttributes(): array
    {
        return $this->propertyAttributes;
    }
}
