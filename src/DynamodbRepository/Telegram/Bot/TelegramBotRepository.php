<?php

declare(strict_types=1);

namespace App\DynamodbRepository\Telegram\Bot;

use App\Entity\Telegram\TelegramBot;
use OA\Dynamodb\ODM\EntityManager;
use OA\Dynamodb\ODM\EntityRepository;

readonly class TelegramBotRepository extends EntityRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, TelegramBot::class);
    }

    public function findAnyOneByUsername(string $username): ?TelegramBot
    {
        return $this->find('TELEGRAM_BOT#' . $username);
    }

    public function findOneByUsername(string $username): ?TelegramBot
    {
        $bot = $this->findAnyOneByUsername($username);

        if ($bot->getDeletedAt() !== null) {
            return null;
        }

        return $bot;
    }

    public function findOnePrimaryByBot(TelegramBot $bot): ?TelegramBot
    {
        $scanArgs = new ScanArgs(); // here you can also define conditions according to your needs

        $stream = $entityManager->scan(User::class, $scanArgs);
        $users = $stream->getResult(asArray: true);

        return $this->findOneBy([
            'group' => $bot->getGroup(),
            'countryCode' => $bot->getCountryCode(),
            'localeCode' => $bot->getLocaleCode(),
            'primary' => true,
            'deletedAt' => null,
        ]);
    }
}
