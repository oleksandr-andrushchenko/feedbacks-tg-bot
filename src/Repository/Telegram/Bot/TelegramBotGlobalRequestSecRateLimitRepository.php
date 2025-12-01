<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBotGlobalRequestSecRateLimit;
use App\Repository\Repository;

/**
 * @extends Repository<TelegramBotGlobalRequestSecRateLimit>
 */
class TelegramBotGlobalRequestSecRateLimitRepository extends Repository
{
    public function __construct(
        TelegramBotGlobalRequestSecRateLimitDynamodbRepository $telegramBotChatRequestMinRateLimitDynamodbRepository,
    )
    {
        parent::__construct(null, $telegramBotChatRequestMinRateLimitDynamodbRepository);
    }

    public function incrementCountBySecond(int $timestamp): TelegramBotGlobalRequestSecRateLimit
    {
        return $this->dynamodb->incrementCountBySecond($timestamp);
    }
}
