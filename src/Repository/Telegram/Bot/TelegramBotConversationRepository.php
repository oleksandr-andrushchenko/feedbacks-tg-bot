<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBotConversation;
use App\Repository\Repository;

/**
 * @extends Repository<TelegramBotConversation>
 */
class TelegramBotConversationRepository extends Repository
{
    public function __construct(
        TelegramBotConversationDoctrineRepository $telegramBotConversationDoctrineRepository,
        TelegramBotConversationDynamodbRepository $telegramBotConversationDynamodbRepository,
    )
    {
        parent::__construct($telegramBotConversationDoctrineRepository, $telegramBotConversationDynamodbRepository);
    }

    public function findOneByHash(string $hash): ?TelegramBotConversation
    {
        return $this->dynamodb->findOneByHash($hash);
    }
}
