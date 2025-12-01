<?php

declare(strict_types=1);

namespace App\Repository\Telegram\Channel;

use App\Entity\Telegram\TelegramBot;
use App\Entity\Telegram\TelegramChannel;
use App\Enum\Telegram\TelegramBotGroupName;
use OA\Dynamodb\ODM\EntityManager;
use OA\Dynamodb\ODM\EntityRepository;
use OA\Dynamodb\ODM\QueryArgs;

/**
 * @extends EntityRepository<TelegramChannel>
 */
class TelegramChannelDynamodbRepository extends EntityRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, TelegramChannel::class);
    }

    public function findAll(): array
    {
        $qb = (new QueryArgs())->indexName('TG_CHANNELS_BY_GROUP_COUNTRY_LOCALE')
            ->keyConditionExpression('tg_channel_pk = :tg_channel_pk')
            ->expressionAttributeValues([':tg_channel_pk' => 'TG_CHANNEL'])
        ;

        return $this->queryMany($qb);
    }

    public function findAnyOneByUsername(string $username): ?TelegramChannel
    {
        $args = (new QueryArgs())->indexName('TG_CHANNELS_BY_USERNAME')
            ->keyConditionExpression('tg_channel_username_pk = :username')
            ->expressionAttributeValues([':username' => $username])
        ;
        return $this->queryOne($args);
    }

    public function findOneByUsername(string $username): ?TelegramChannel
    {
        $channel = $this->findAnyOneByUsername($username);

        if ($channel->getDeletedAt() !== null) {
            return null;
        }

        return $channel;
    }

    public function findOnePrimaryByBot(TelegramBot $bot): ?TelegramChannel
    {
        $args = (new QueryArgs())->indexName('TG_CHANNELS_BY_GROUP_COUNTRY_LOCALE')
            ->keyConditionExpression('tg_channel_pk = :tg_channel_pk AND tg_channel_group_country_locale_sk = :tg_channel_group_country_locale_sk')
            ->expressionAttributeValues([
                ':tg_channel_pk' => 'TG_CHANNEL',
                ':tg_channel_group_country_locale_sk' => $bot->getGroup()->name . '#' . $bot->getCountryCode() . '#' . $bot->getLocaleCode(),
            ])
        ;
        $channels = $this->queryMany($args);
        foreach ($channels as $channel) {
            if ($channel->getDeletedAt() !== null) {
                continue;
            }
            if ($channel->getPrimary() !== true) {
                continue;
            }
            return $channel;
        }

        return null;
    }

    public function findOnePrimaryByChannel(TelegramChannel $channel): ?TelegramChannel
    {
        $args = (new QueryArgs())->indexName('TG_CHANNELS_BY_GROUP_COUNTRY_LOCALE')
            ->keyConditionExpression('tg_channel_pk = :tg_channel_pk AND tg_channel_group_country_locale_sk = :tg_channel_group_country_locale_sk')
            ->expressionAttributeValues([
                ':tg_channel_pk' => 'TG_CHANNEL',
                ':tg_channel_group_country_locale_sk' => $channel->getGroup()->name . '#' . $channel->getCountryCode() . '#' . $channel->getLocaleCode(),
            ])
        ;
        $channels = $this->queryMany($args);
        foreach ($channels as $channel_) {
            if ($channel_->getLevel1RegionId() !== $channel->getLevel1RegionId()) {
                continue;
            }
            if ($channel_->getDeletedAt() !== null) {
                continue;
            }
            if ($channel_->getPrimary() !== true) {
                continue;
            }
            return $channel_;
        }

        return null;
    }

    /**
     * @param TelegramBotGroupName $group
     * @param string $countryCode
     * @return TelegramChannel[]
     */
    public function findPrimaryByGroupAndCountry(TelegramBotGroupName $group, string $countryCode): array
    {
        $args = (new QueryArgs())->indexName('TG_CHANNELS_BY_GROUP_COUNTRY_LOCALE')
            ->keyConditionExpression('tg_channel_pk = :tg_channel_pk AND tg_channel_group_country_locale_sk = :tg_channel_group_country_locale_sk')
            ->expressionAttributeValues([
                ':tg_channel_pk' => 'TG_CHANNEL',
                ':tg_channel_group_country_locale_sk' => $group->name . '#' . $countryCode . '#',
            ])
        ;
        $channels = $this->queryMany($args);

        return array_filter($channels, static fn (TelegramChannel $channel): bool => $channel->getDeletedAt() === null && $channel->getPrimary() === true);
    }
}
