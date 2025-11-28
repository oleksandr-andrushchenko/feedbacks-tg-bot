<?php

declare(strict_types=1);

namespace OA\Dynamodb\ODM;

use Generator;

/**
 * @template T
 */
readonly class ResultStream
{
    /**
     * @param Generator<T> $result
     */
    public function __construct(
        protected Generator $result,
    ) {
    }

    /**
     * @return Generator<T>|array<T>
     */
    public function getResult(bool $asArray = false): iterable
    {
        return $asArray ? iterator_to_array($this->result) : $this->result;
    }
}
