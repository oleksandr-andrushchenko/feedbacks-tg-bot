<?php

declare(strict_types=1);

namespace App\Repository\Intl;

use App\Entity\Intl\Level1Region;
use OA\Dynamodb\ODM\EntityManager;
use OA\Dynamodb\ODM\EntityRepository;
use OA\Dynamodb\ODM\QueryArgs;

/**
 * @extends EntityRepository<Level1Region>
 */
class Level1RegionDynamodbRepository extends EntityRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, Level1Region::class);
    }

    public function findAll(): array
    {
        $qb = (new QueryArgs())->indexName('LVL_1_RGN_BY_COUNTRY_NAME')
            ->keyConditionExpression('lvl_1_rgn_pk = :lvl_1_rgn_pk')
            ->expressionAttributeValues([':lvl_1_rgn_pk' => 'LVL_1_RGN'])
        ;

        return $this->queryMany($qb);
    }

    public function findOneByCountryAndName(string $countryCode, string $name): ?Level1Region
    {
        $args = (new QueryArgs())->indexName('LVL_1_RGN_BY_COUNTRY_NAME')
            ->keyConditionExpression('lvl_1_rgn_pk = :lvl_1_rgn_pk AND lvl_1_rgn_country_name_sk = :lvl_1_rgn_country_name_sk')
            ->expressionAttributeValues([
                ':lvl_1_rgn_pk' => 'LVL_1_RGN',
                ':lvl_1_rgn_country_name_sk' => $countryCode . '#' . $name,
            ])
        ;

        return $this->queryOne($args);
    }

    public function findByCountry(string $countryCode): array
    {
        $args = (new QueryArgs())->indexName('LVL_1_RGN_BY_COUNTRY_NAME')
            ->keyConditionExpression('lvl_1_rgn_pk = :lvl_1_rgn_pk AND begins_with(lvl_1_rgn_country_name_sk, :lvl_1_rgn_country_name_sk)')
            ->expressionAttributeValues([
                ':lvl_1_rgn_pk' => 'LVL_1_RGN',
                ':lvl_1_rgn_country_name_sk' => $countryCode . '#',
            ])
        ;

        return $this->queryMany($args);
    }
}
