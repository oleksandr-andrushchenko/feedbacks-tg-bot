<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBotConversation;
use OA\Dynamodb\ODM\EntityManager;
use OA\Dynamodb\ODM\EntityRepository;

/**
 * @extends EntityRepository<TelegramBotConversation>
 */
class TelegramBotConversationDynamodbRepository extends EntityRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, TelegramBotConversation::class);
    }

    public function findOneByHash(string $hash): ?TelegramBotConversation
    {
        return $this->get(['hash' => $hash]);
    }
}
