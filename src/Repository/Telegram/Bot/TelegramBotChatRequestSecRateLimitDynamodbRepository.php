<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBotChatRequestSecRateLimit;
use DateTimeImmutable;
use OA\Dynamodb\ODM\EntityManager;
use OA\Dynamodb\ODM\EntityRepository;
use OA\Dynamodb\ODM\UpdateArgs;

/**
 * @extends EntityRepository<TelegramBotChatRequestSecRateLimit>
 */
class TelegramBotChatRequestSecRateLimitDynamodbRepository extends EntityRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, TelegramBotChatRequestSecRateLimit::class);
    }

    public function incrementCountByChatAndSecond(int $chatId, int $second): TelegramBotChatRequestSecRateLimit
    {
        $args = (new UpdateArgs())
            ->updateExpression('
                SET #chatId = if_not_exists(#chatId, :chatId),
                #second = if_not_exists(#second, :second),
                #expireAt = if_not_exists(#expireAt, :expireAt)
                ADD #count :countInc
            ')
            ->expressionAttributeNames([
                '#chatId' => 'chat_id',
                '#second' => 'second',
                '#count' => 'count',
                '#expireAt' => 'expire_at',
            ])
            ->expressionAttributeValues([
                ':chatId' => $second,
                ':second' => $second,
                ':countInc' => 1,
                ':expireAt' => (new DateTimeImmutable())->setTimestamp(time() + 2)->format('c'),
            ])
        ;

        return $this->updateOneByQueryReturn($args, [
            'chatId' => $chatId,
            'second' => $second,
        ]);
    }
}
