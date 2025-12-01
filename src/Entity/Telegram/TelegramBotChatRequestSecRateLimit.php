<?php

declare(strict_types=1);

namespace App\Entity\Telegram;

use DateTimeInterface;
use OA\Dynamodb\Attribute\Attribute;
use OA\Dynamodb\Attribute\Entity;
use OA\Dynamodb\Attribute\PartitionKey;
use OA\Dynamodb\Attribute\SortKey;

#[Entity(
    new PartitionKey('TG_BOT_REQ_RL', ['chatId']),
    new SortKey('SEC', ['second']),
)]
class TelegramBotChatRequestSecRateLimit
{
    public function __construct(
        #[Attribute('chat_id')]
        private readonly int $chatId,
        #[Attribute]
        private readonly int $second,
        #[Attribute]
        private readonly int $count = 0,
        #[Attribute('expire_at')]
        private ?DateTimeInterface $expireAt = null,
    )
    {
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getSecond(): int
    {
        return $this->second;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getExpireAt(): DateTimeInterface
    {
        return $this->expireAt;
    }
}
