<?php

declare(strict_types=1);

namespace OA\Dynamodb\ODM;

use Psr\Log\LoggerInterface;

/**
 * @template T of object
 */
class EntityRepository
{
    /**
     * @param EntityManager $em
     * @param class-string<T> $entityClass The class name of the entity this repository manages
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        protected EntityManager $em,
        protected string $entityClass,
        protected ?LoggerInterface $logger = null,
    )
    {
    }

    /**
     * @param array $keyFieldValues
     * @return T|null
     * @throws EntityManagerException
     */
    public function get(array $keyFieldValues): ?object
    {
        return $this->em->get($this->entityClass, $keyFieldValues);
    }

    /**
     * @param QueryArgs $queryArgs
     * @return T|null
     * @throws EntityManagerException
     */
    public function queryOne(QueryArgs $queryArgs): ?object
    {
        return $this->em->queryOne($this->entityClass, $queryArgs);
    }

    /**
     * @param QueryArgs $queryArgs
     * @return array<T>
     * @throws EntityManagerException
     */
    public function queryMany(QueryArgs $queryArgs): array
    {
        return $this->em->query($this->entityClass, $queryArgs)->getResult(true);
    }

    public function updateOneByQueryReturn(UpdateArgs $updateArgs, array $keyFieldValues): ?object
    {
        return $this->em->updateOneByQueryReturn($this->entityClass, $updateArgs, $keyFieldValues);
    }

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
