<?php

declare(strict_types=1);

namespace App\Service\ORM;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManager;
use OA\Dynamodb\ODM\EntityManager as DynamodbEntityManager;

class EntityManager
{
    public function __construct(
        private DoctrineEntityManager $doctrine,
        private DynamodbEntityManager $dynamodb,
    )
    {
    }

    public function doctrine(): DoctrineEntityManager
    {
        return $this->doctrine;
    }

    public function dynamodb(): DynamodbEntityManager
    {
        return $this->dynamodb;
    }

    public function persist(object $object): void
    {
        $this->dynamodb->persist($object);
    }

    public function flush(): void
    {
        $this->dynamodb->flush();
    }

    public function remove(object $object): void
    {
        $this->dynamodb->delete($object);
    }
}