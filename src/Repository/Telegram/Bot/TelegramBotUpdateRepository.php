<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBotUpdate;
use App\Repository\Repository;

/**
 * @extends Repository<TelegramBotUpdate>
 */
class TelegramBotUpdateRepository extends Repository
{
    public function __construct(
        TelegramBotUpdateDoctrineRepository $telegramBotUpdateDoctrineRepository,
        TelegramBotUpdateDynamodbRepository $telegramBotUpdateDynamodbRepository,
    )
    {
        parent::__construct($telegramBotUpdateDoctrineRepository, $telegramBotUpdateDynamodbRepository);
    }

    public function findOneByUpdateId($updateId): ?TelegramBotUpdate
    {
        return $this->dynamodb->findOneByUpdateId($updateId);
    }
}
