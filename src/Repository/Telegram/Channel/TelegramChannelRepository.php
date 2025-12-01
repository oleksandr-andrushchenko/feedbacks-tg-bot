<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Channel;

use App\Entity\Telegram\TelegramBot;
use App\Entity\Telegram\TelegramChannel;
use App\Enum\Telegram\TelegramBotGroupName;
use App\Repository\Repository;

/**
 * @extends Repository<TelegramChannel>
 * @method TelegramChannelDoctrineRepository doctrine()
 * @property TelegramChannelDoctrineRepository doctrine
 * @method TelegramChannelDynamodbRepository dynamodb()
 * @property TelegramChannelDynamodbRepository dynamodb
 */
class TelegramChannelRepository extends Repository
{
    public function __construct(
        TelegramChannelDoctrineRepository $telegramChannelDoctrineRepository,
        TelegramChannelDynamodbRepository $telegramChannelDynamodbRepository,
    )
    {
        parent::__construct($telegramChannelDoctrineRepository, $telegramChannelDynamodbRepository);
    }

    public function findAll(): array
    {
        return $this->dynamodb->findAll();
    }

    public function findAnyOneByUsername(string $username): ?TelegramChannel
    {
        return $this->dynamodb->findAnyOneByUsername($username);
    }

    public function findOneByUsername(string $username): ?TelegramChannel
    {
        return $this->dynamodb->findOneByUsername($username);
    }

    public function findOnePrimaryByBot(TelegramBot $bot): ?TelegramChannel
    {
        return $this->dynamodb->findOnePrimaryByBot($bot);
    }

    public function findOnePrimaryByChannel(TelegramChannel $channel): ?TelegramChannel
    {
        return $this->dynamodb->findOnePrimaryByChannel($channel);
    }

    public function findPrimaryByGroupAndCountry(TelegramBotGroupName $group, string $countryCode): array
    {
        return $this->dynamodb->findPrimaryByGroupAndCountry($group, $countryCode);
    }
}
