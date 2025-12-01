<?php

declare(strict_types=1);

namespace App\Repository\Intl;

use App\Entity\Intl\Level1Region;
use App\Repository\Repository;

/**
 * @extends Repository<Level1Region>
 * @method Level1RegionDoctrineRepository doctrine()
 * @property Level1RegionDoctrineRepository doctrine
 * @method Level1RegionDynamodbRepository dynamodb()
 * @property Level1RegionDynamodbRepository $dynamodb
 */
class Level1RegionRepository extends Repository
{
    public function __construct(
        Level1RegionDoctrineRepository $level1RegionDoctrineRepository,
        Level1RegionDynamodbRepository $level1RegionDynamodbRepository,
    )
    {
        parent::__construct($level1RegionDoctrineRepository, $level1RegionDynamodbRepository);
    }

    public function findOneByCountryAndName(string $countryCode, string $name): ?Level1Region
    {
        return $this->dynamodb->findOneByCountryAndName($countryCode, $name);
    }

    public function findByCountry(string $countryCode): array
    {
        return $this->dynamodb->findByCountry($countryCode);
    }
}
