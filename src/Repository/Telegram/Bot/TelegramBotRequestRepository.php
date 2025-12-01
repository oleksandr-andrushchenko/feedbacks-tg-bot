<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBotRequest;
use App\Entity\Telegram\TelegramBotRequestLimits;
use App\Repository\Repository;

/**
 * @extends Repository<TelegramBotRequest>
 */
class TelegramBotRequestRepository extends Repository
{
    public function __construct(
        TelegramBotRequestDoctrineRepository $telegramBotRequestDoctrineRepository,
    )
    {
        parent::__construct($telegramBotRequestDoctrineRepository, null);
    }

    public function getLimits(null|int|string $chatId): ?TelegramBotRequestLimits
    {
        return $this->doctrine->getLimits($chatId);
    }
}
