<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBotChatRequestMinRateLimit;
use App\Repository\Repository;

/**
 * @extends Repository<TelegramBotChatRequestMinRateLimit>
 */
class TelegramBotChatRequestMinRateLimitRepository extends Repository
{
    public function __construct(
        TelegramBotChatRequestMinRateLimitDynamodbRepository $telegramBotChatRequestMinRateLimitDynamodbRepository,
    )
    {
        parent::__construct(null, $telegramBotChatRequestMinRateLimitDynamodbRepository);
    }

    public function incrementCountByChatAndMinute(int $chatId, int $minute): TelegramBotChatRequestMinRateLimit
    {
        return $this->dynamodb->incrementCountByChatAndMinute($chatId, $minute);
    }
}
