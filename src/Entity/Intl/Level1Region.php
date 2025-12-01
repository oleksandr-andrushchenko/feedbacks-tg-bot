<?php

declare(strict_types=1);

namespace App\Entity\Intl;

use OA\Dynamodb\Attribute\Attribute;
use OA\Dynamodb\Attribute\Entity;
use OA\Dynamodb\Attribute\GlobalIndex;
use OA\Dynamodb\Attribute\PartitionKey;
use OA\Dynamodb\Attribute\SortKey;

#[Entity(
    new PartitionKey('LVL_1_RGN', ['id']),
    new SortKey('META'),
    [
        new GlobalIndex(
            'LVL_1_RGN_BY_COUNTRY_NAME',
            new PartitionKey('LVL_1_RGN', [], 'lvl_1_rgn_pk'),
            new SortKey(null, ['countryCode', 'name'], 'lvl_1_rgn_country_name_sk'),
        ),
    ]
)]
class Level1Region
{
    public function __construct(
        #[Attribute('lvl_1_rgn_id')]
        private string $id,
        #[Attribute('country_code')]
        private readonly string $countryCode,
        #[Attribute]
        private readonly string $name,
        #[Attribute]
        private ?string $timezone = null,
    )
    {
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }
}
