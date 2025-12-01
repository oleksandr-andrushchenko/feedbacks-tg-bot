<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Bot;

use App\Entity\Telegram\TelegramBot;
use App\Enum\Telegram\TelegramBotGroupName;
use OA\Dynamodb\ODM\EntityManager;
use OA\Dynamodb\ODM\EntityRepository;
use OA\Dynamodb\ODM\QueryArgs;

/**
 * @extends EntityRepository<TelegramBot>
 */
class TelegramBotDynamodbRepository extends EntityRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, TelegramBot::class);
    }

    public function findAll(): array
    {
        $qb = (new QueryArgs())->indexName('TG_BOTS_BY_GROUP_COUNTRY_LOCALE')
            ->keyConditionExpression('tg_bot_pk = :tg_bot_pk')
            ->expressionAttributeValues([':tg_bot_pk' => 'TG_BOT'])
        ;

        return $this->queryMany($qb);
    }

    public function findAnyOneByUsername(string $username): ?TelegramBot
    {
        $args = (new QueryArgs())->indexName('TG_BOTS_BY_USERNAME')
            ->keyConditionExpression('tg_bot_username_pk = :username')
            ->expressionAttributeValues([':username' => $username])
        ;
        return $this->queryOne($args);
    }

    public function findOneByUsername(string $username): ?TelegramBot
    {
        $bot = $this->findAnyOneByUsername($username);

        if ($bot->getDeletedAt() !== null) {
            return null;
        }

        return $bot;
    }

    public function findByGroup(TelegramBotGroupName $group): array
    {
        $args = (new QueryArgs())->indexName('TG_BOTS_BY_GROUP_COUNTRY_LOCALE')
            ->keyConditionExpression('tg_bot_pk = :tg_bot_pk AND begins_with(tg_bot_group_country_locale_sk, :tg_bot_group_country_locale_sk)')
            ->expressionAttributeValues([
                ':tg_bot_pk' => 'TG_BOT',
                ':tg_bot_group_country_locale_sk' => $group->name . '#',
            ])
        ;

        return $this->skipDeleted($this->queryMany($args));
    }

    public function findPrimaryByGroup(TelegramBotGroupName $group): array
    {
        $args = (new QueryArgs())->indexName('TG_BOTS_BY_GROUP_PRIMARY')
            ->keyConditionExpression('tg_bot_pk = :tg_bot_pk AND tg_bot_group_primary_sk = :tg_bot_group_primary_sk')
            ->expressionAttributeValues([
                ':tg_bot_pk' => 'TG_BOT',
                ':tg_bot_group_primary_sk' => $group->name . '#' . true,
            ])
        ;

        return $this->skipDeleted($this->queryMany($args));
    }

    public function findByGroupAndCountry(TelegramBotGroupName $group, string $countryCode): array
    {
        $args = (new QueryArgs())->indexName('TG_BOTS_BY_GROUP_COUNTRY_LOCALE')
            ->keyConditionExpression('tg_bot_pk = :tg_bot_pk AND begins_with(tg_bot_group_country_locale_sk, :tg_bot_group_country_locale_sk)')
            ->expressionAttributeValues([
                ':tg_bot_pk' => 'TG_BOT',
                ':tg_bot_group_country_locale_sk' => $group->name . '#' . $countryCode . '#',
            ])
        ;

        return $this->skipDeleted($this->queryMany($args));
    }

    public function findOnePrimaryByBot(TelegramBot $bot): ?TelegramBot
    {
        $args = (new QueryArgs())->indexName('TG_BOTS_BY_GROUP_COUNTRY_LOCALE')
            ->keyConditionExpression('tg_bot_pk = :tg_bot_pk AND tg_bot_group_country_locale_sk = :tg_bot_group_country_locale_sk')
            ->expressionAttributeValues([
                ':tg_bot_pk' => 'TG_BOT',
                ':tg_bot_group_country_locale_sk' => $bot->getGroup()->name . '#' . $bot->getCountryCode() . '#' . $bot->getLocaleCode(),
            ])
        ;
        $bots = $this->queryMany($args);
        foreach ($bots as $bot) {
            if ($bot->getDeletedAt() !== null) {
                continue;
            }
            if ($bot->getPrimary() !== true) {
                continue;
            }
            return $bot;
        }

        return null;
    }

    public function findPrimaryByGroupAndIds(TelegramBotGroupName $group, array $ids): array
    {
        $bots = $this->findPrimaryByGroup($group);
        return array_filter($bots, static fn (TelegramBot $bot): bool => in_array($bot->getId(), $ids, true));
    }

    private function skipDeleted($bots): array
    {
        return array_filter($bots, static fn (TelegramBot $bot): bool => $bot->getDeletedAt() === null);
    }
}
