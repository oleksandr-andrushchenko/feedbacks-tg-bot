<?php

declare(strict_types=1);

namespace App\Tests\Traits\Telegram\Bot;

use App\Repository\Telegram\Bot\TelegramBotConversationDoctrineRepository;

trait TelegramBotConversationRepositoryProviderTrait
{
    public function getTelegramBotConversationRepository(): TelegramBotConversationDoctrineRepository
    {
        return static::getContainer()->get('app.telegram_bot_conversation_repository');
    }
}