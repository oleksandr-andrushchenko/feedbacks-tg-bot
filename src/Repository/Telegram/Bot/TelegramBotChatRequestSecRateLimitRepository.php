<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBotChatRequestSecRateLimit;
use App\Repository\Repository;

/**
 * @extends Repository<TelegramBotChatRequestSecRateLimit>
 */
class TelegramBotChatRequestSecRateLimitRepository extends Repository
{
    public function __construct(
        TelegramBotChatRequestSecRateLimitDynamodbRepository $telegramBotChatRequestMinRateLimitDynamodbRepository,
    )
    {
        parent::__construct(null, $telegramBotChatRequestMinRateLimitDynamodbRepository);
    }

    public function incrementCountByChatAndSecond(int $chatId, int $second): TelegramBotChatRequestSecRateLimit
    {
        return $this->dynamodb->incrementCountByChatAndSecond($chatId, $second);
    }
}
