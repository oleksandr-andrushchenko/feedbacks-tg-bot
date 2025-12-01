<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\User\User;
use App\Repository\Repository;

/**
 * @extends Repository<User>
 * @method UserDoctrineRepository doctrine()
 * @property UserDoctrineRepository doctrine
 * @method UserDynamodbRepository dynamodb()
 * @property UserDynamodbRepository dynamodb
 */
class UserRepository extends Repository
{
    public function __construct(
        UserDoctrineRepository $userDoctrineRepository,
        UserDynamodbRepository $userDynamodbRepository,
    )
    {
        parent::__construct($userDoctrineRepository, $userDynamodbRepository);
    }
}
