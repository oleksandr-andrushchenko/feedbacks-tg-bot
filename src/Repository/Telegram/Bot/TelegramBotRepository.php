<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBot;
use App\Enum\Telegram\TelegramBotGroupName;
use App\Repository\Repository;

/**
 * @extends Repository<TelegramBot>
 * @method TelegramBotDoctrineRepository doctrine()
 * @property TelegramBotDoctrineRepository doctrine
 * @method TelegramBotDynamodbRepository dynamodb()
 * @property  TelegramBotDynamodbRepository dynamodb
 */
class TelegramBotRepository extends Repository
{
    public function __construct(
        TelegramBotDoctrineRepository $telegramBotDoctrineRepository,
        TelegramBotDynamodbRepository $telegramBotDynamodbRepository,
    )
    {
        parent::__construct($telegramBotDoctrineRepository, $telegramBotDynamodbRepository);
    }

    public function findAll(): array
    {
        return $this->dynamodb->findAll();
    }

    public function findAnyOneByUsername(string $username): ?TelegramBot
    {
        return $this->dynamodb->findAnyOneByUsername($username);
    }

    public function findOneByUsername(string $username): ?TelegramBot
    {
        return $this->dynamodb->findOneByUsername($username);
    }

    public function findByGroup(TelegramBotGroupName $group): array
    {
        return $this->dynamodb->findByGroup($group);
    }

    public function findPrimaryByGroup(TelegramBotGroupName $group): array
    {
        return $this->dynamodb->findPrimaryByGroup($group);
    }

    public function findByGroupAndCountry(TelegramBotGroupName $group, string $countryCode): array
    {
        return $this->dynamodb->findByGroupAndCountry($group, $countryCode);
    }

    public function findOnePrimaryByBot(TelegramBot $bot): ?TelegramBot
    {
        return $this->dynamodb->findOnePrimaryByBot($bot);
    }

    public function findPrimaryByGroupAndIds(TelegramBotGroupName $group, array $ids): array
    {
        return $this->dynamodb->findPrimaryByGroupAndIds($group, $ids);
    }
}
