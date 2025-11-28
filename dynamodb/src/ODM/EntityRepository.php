<?php

declare(strict_types=1);

namespace OA\Dynamodb\ODM;

/**
 * @template T of object
 */
readonly class EntityRepository
{
    /**
     * @param EntityManager $em
     * @param class-string<T> $entityClass The class name of the entity this repository manages
     */
    public function __construct(
        protected EntityManager $em,
        protected string $entityClass,
    )
    {
    }

    /**
     * @param string $pk
     * @param null|string $sk
     * @return T|null
     * @throws EntityManagerException
     */
    public function find(string $pk, string $sk = null): ?object
    {
        return $this->em->find($this->entityClass, $pk, $sk);
    }
}
