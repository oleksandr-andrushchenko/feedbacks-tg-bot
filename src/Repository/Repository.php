<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository as DoctrineRepository;
use OA\Dynamodb\ODM\EntityRepository as DynamodbRepository;

/**
 * @template T of object
 */
class Repository
{
    public function __construct(
        protected ?DoctrineRepository $doctrine,
        protected ?DynamodbRepository $dynamodb,
    )
    {
    }

    public function doctrine(): ?DoctrineRepository
    {
        return $this->doctrine;
    }

    public function dynamodb(): ?DynamodbRepository
    {
        return $this->dynamodb;
    }

    /**
     * @param array $keyFieldValues
     * @return T|null
     */
    public function find(array $keyFieldValues): ?object
    {
        return $this->dynamodb?->get($keyFieldValues);
    }
}