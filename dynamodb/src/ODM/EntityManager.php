<?php

declare(strict_types=1);

namespace OA\Dynamodb\ODM;

use Aws\DynamoDb\DynamoDbClient;
use Generator;
use OA\Dynamodb\Metadata\MetadataLoader;
use OA\Dynamodb\Serializer\EntitySerializer;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @template T of object
 */
readonly class EntityManager
{
    public function __construct(
        protected DynamoDbClient $dynamoDbClient,
        protected MetadataLoader $metadataLoader,
        protected EntitySerializer $entitySerializer,
        protected OpArgsBuilder $opArgsBuilder,
        protected ?LoggerInterface $logger = null,
    )
    {
    }

    public function getClient(): DynamoDbClient
    {
        return $this->dynamoDbClient;
    }

    public function getOpArgsBuilder(): OpArgsBuilder
    {
        return $this->opArgsBuilder;
    }

    /**
     * @param class-string<T> $class
     * @param array<string, mixed> $keyFieldValues
     * @return T|null
     * @throws EntityManagerException
     */
    public function get(string $class, array $keyFieldValues): ?object
    {
        try {
            $key = $this->entitySerializer->serializePrimaryKey($class, $keyFieldValues);
            $table = $this->metadataLoader->getEntityMetadata($class)->getTable();

            $result = $this->dynamoDbClient->getItem([
                'TableName' => $table,
                'Key' => $key,
            ]);

            $rawItem = $result['Item'] ?? null;

            if (null === $rawItem) {
                return null;
            }

            return $this->entitySerializer->deserialize($rawItem, $class);
        } catch (Throwable $exception) {
            $this->wrapException($exception);
        }
    }

    /**
     * @param class-string<T> $class
     * @return T|null
     * @throws EntityManagerException
     */
    public function queryOne(string $class, QueryArgs $queryArgs): ?object
    {
        $queryArgs->limit(1);

        /** @var array<int, T> $result */
        $result = $this->query($class, $queryArgs)->getResult(true);

        return $result[0] ?? null;
    }

    /**\
     * @param class-string<T> $class
     * @return ResultStream<T>
     * @throws EntityManagerException
     */
    public function query(string $class, QueryArgs $queryArgs): ResultStream
    {
        $result = (function () use ($class, $queryArgs): Generator {
            try {
                $table = $this->metadataLoader->getEntityMetadata($class)->getTable();
                $queryArgs->tableName($table);

                $params = $this->opArgsBuilder->serialize($queryArgs);
                $remainingLimit = $params['Limit'] ?? null;

                do {
                    if (null !== $remainingLimit) {
                        $params['Limit'] = $remainingLimit;
                    }

                    $result = $this->dynamoDbClient->query($params);

                    foreach ($result->get('Items') ?? [] as $item) {
                        yield $this->entitySerializer->deserialize($item, $class);

                        if (null === $remainingLimit) {
                            continue;
                        }

                        if (0 >= --$remainingLimit) {
                            return;
                        }
                    }

                    $params['ExclusiveStartKey'] = $result->get('LastEvaluatedKey') ?? null;
                } while (!empty($params['ExclusiveStartKey']));
            } catch (Throwable $exception) {
                $this->wrapException($exception);
            }
        })();

        return new ResultStream($result);
    }

    /**
     * @param class-string<T> $class
     * @return ResultStream<T>
     * @throws EntityManagerException
     */
    public function scan(string $class, ScanArgs $scanArgs): ResultStream
    {
        $result = (function () use ($class, $scanArgs): Generator {
            try {
                $table = $this->metadataLoader->getEntityMetadata($class)->getTable();
                $scanArgs->tableName($table);

                $params = $this->opArgsBuilder->serialize($scanArgs);
                $remainingLimit = $params['Limit'] ?? null;

                do {
                    if (null !== $remainingLimit) {
                        $params['Limit'] = $remainingLimit;
                    }

                    $result = $this->dynamoDbClient->scan($params);

                    foreach ($result->get('Items') ?? [] as $item) {
                        yield $this->entitySerializer->deserialize($item, $class);

                        if (null === $remainingLimit) {
                            continue;
                        }

                        if (0 >= --$remainingLimit) {
                            return;
                        }
                    }

                    $params['ExclusiveStartKey'] = $result->get('LastEvaluatedKey') ?? null;
                } while (!empty($params['ExclusiveStartKey']));
            } catch (Throwable $exception) {
                $this->wrapException($exception);
            }
        })();

        return new ResultStream($result);
    }

    /**
     * @param T $entity
     * @throws EntityManagerException
     */
    public function put(object $entity): void
    {
        try {
            $table = $this->metadataLoader->getEntityMetadata($entity::class)->getTable();
            $item = $this->entitySerializer->serialize($entity);

            $this->dynamoDbClient->putItem([
                'TableName' => $table,
                'Item' => $item,
            ]);
        } catch (Throwable $exception) {
            $this->wrapException($exception);
        }
    }

    /**
     * @param string $class
     * @param UpdateArgs $updateArgs
     * @param array $keyFieldValues
     * @return T|null
     * @throws EntityManagerException
     */
    public function updateOneByQueryReturn(string $class, UpdateArgs $updateArgs, array $keyFieldValues): ?object
    {
        try {
            $key = $this->entitySerializer->serializePrimaryKey($class, $keyFieldValues);
            $updateArgs->key($key);

            $table = $this->metadataLoader->getEntityMetadata($class)->getTable();
            $updateArgs->tableName($table)->returnValues('ALL_NEW');

            $params = $this->opArgsBuilder->serialize($updateArgs);

            $result = $this->dynamoDbClient->updateItem($params);

            $rawItem = $result['Attributes'] ?? null;
            if (null === $rawItem) {
                return null;
            }

            return $this->entitySerializer->deserialize($rawItem, $class);
        } catch (Throwable $exception) {
            $this->wrapException($exception);
        }
    }

    /**
     * @param T $entity
     * @throws EntityManagerException
     */
    public function delete(object $entity): void
    {
        try {
            $table = $this->metadataLoader->getEntityMetadata($entity::class)->getTable();
            $key = $this->entitySerializer->serializePrimaryKey($entity);

            $this->dynamoDbClient->deleteItem([
                'TableName' => $table,
                'Key' => $key,
            ]);
        } catch (Throwable $exception) {
            $this->wrapException($exception);
        }
    }

    /**
     * @param class-string $class
     * @return ResultStream<mixed>
     * @throws EntityManagerException
     */
    public function describe(string $class): ResultStream
    {
        $result = (function () use ($class): Generator {
            try {
                $table = $this->metadataLoader->getEntityMetadata($class)->getTable();
                yield from $this->dynamoDbClient->describeTable(['TableName' => $table]);
            } catch (Throwable $exception) {
                $this->wrapException($exception);
            }
        })();

        return new ResultStream($result);
    }

    /**
     * @template T of object
     * @param T $entity
     * @throws EntityManagerException
     * @todo: use implement UoW
     */
    public function persist(object $entity): void
    {
        $this->put($entity);
    }

    /**
     * @return void
     * @todo: use implement UoW
     */
    public function flush(): void
    {
    }

    /**
     * @throws EntityManagerException
     */
    private function wrapException(Throwable $exception): never
    {
        $this->logger?->error($exception);
        throw new EntityManagerException(
            sprintf('An error occurred. %s: %s', $exception::class, $exception->getMessage())
        );
    }
}
