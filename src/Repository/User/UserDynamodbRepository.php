<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\User\User;
use OA\Dynamodb\ODM\EntityManager;
use OA\Dynamodb\ODM\EntityRepository;

/**
 * @extends EntityRepository<User>
 */
class UserDynamodbRepository extends EntityRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, User::class);
    }
}
