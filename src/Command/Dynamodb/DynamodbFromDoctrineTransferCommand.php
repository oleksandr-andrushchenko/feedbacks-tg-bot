<?php

declare(strict_types=1);

namespace App\Command\Dynamodb;

use App\Entity\User\User;
use App\Repository\Intl\Level1RegionRepository;
use App\Repository\Telegram\Bot\TelegramBotRepository;
use App\Repository\Telegram\Channel\TelegramChannelRepository;
use App\Repository\User\UserRepository;
use App\Service\IdGenerator;
use App\Service\Intl\CountryProvider;
use App\Service\Intl\Level1RegionProvider;
use App\Service\Intl\LocaleProvider;
use App\Service\ORM\EntityManager;
use App\Service\Telegram\Bot\TelegramBotCreator;
use App\Service\Telegram\Channel\TelegramChannelCreator;
use App\Transfer\Telegram\TelegramBotTransfer;
use App\Transfer\Telegram\TelegramChannelTransfer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @see DynamodbFromDoctrineTransferCommand
 */
class DynamodbFromDoctrineTransferCommand extends Command
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly TelegramBotRepository $telegramBotRepository,
        private readonly TelegramChannelRepository $telegramChannelRepository,
        private readonly Level1RegionRepository $level1RegionRepository,
        private readonly UserRepository $userRepository,
        private TelegramBotCreator $telegramBotCreator,
        private TelegramChannelCreator $telegramChannelCreator,
        private readonly CountryProvider $countryProvider,
        private readonly LocaleProvider $localeProvider,
        private readonly Level1RegionProvider $level1RegionProvider,
        private readonly IdGenerator $idGenerator,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Load data from doctrine to your dynamodb database')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // todo: use $level1RegionIdMap
        $level1RegionIdMap = [];
        // todo: use $userIdMap
        $userIdMap = [];

        $stats = [
            'level_1_regions' => $this->transferLevel1Regions($level1RegionIdMap),
            'users' => $this->transferUsers($level1RegionIdMap, $userIdMap),
            'telegram_bots' => $this->transferTelegramBots(),
            'telegram_channels' => $this->transferTelegramChannels(),
            // todo: telegram_payment_methods, users
        ];

        $this->entityManager->dynamodb()->flush();

        $io->section('Transfer Summary');
        $io->table(
            ['Entity', 'Affected Rows'],
            array_map(
                fn ($key, $value) => [$key, $value],
                array_keys($stats),
                $stats
            )
        );
        $io->success('Transfer completed.');

        return Command::SUCCESS;
    }

    private function transferLevel1Regions(array &$level1RegionIdMap): int
    {
        $affectedRows = 0;
        foreach ($this->level1RegionRepository->doctrine()->findAll() as $level1Region) {
            $oldId = $level1Region->getId();
            $newId = $this->idGenerator->generateUuid();
            $level1RegionIdMap[$oldId] = $newId;
            $level1RegionCopy = clone $level1Region;
            $level1RegionCopy->setId($newId);
            $this->entityManager->dynamodb()->persist($level1RegionCopy);
            $affectedRows++;
        }

        return $affectedRows;
    }

    private function transferUsers(array $level1RegionIdMap, array &$userIdMap): int
    {
        $affectedRows = 0;
        foreach ($this->userRepository->doctrine()->findAll() as $user) {
            /** @var User $user */
            $oldId = $user->getId();
            $newId = $this->idGenerator->generateUuid();
            $userIdMap[$oldId] = $newId;
            $userCopy = (clone $user);
            $userCopy->setId($newId)
                ->setLevel1RegionId($level1RegionIdMap[$user->getLevel1RegionId()])
            ;
            $this->entityManager->dynamodb()->persist($userCopy);
            $affectedRows++;
        }

        return $affectedRows;
    }

    private function transferTelegramBots(): int
    {
        $affectedRows = 0;
        foreach ($this->telegramBotRepository->doctrine()->findAll() as $telegramBot) {
            $telegramBotTransfer = new TelegramBotTransfer(
                username: $telegramBot->getUsername(),
                group: $telegramBot->getGroup(),
                groupPassed: true,
                name: $telegramBot->getName(),
                namePassed: true,
                token: $telegramBot->getToken(),
                tokenPassed: true,
                country: $telegramBot->getCountryCode() === null ? null : $this->countryProvider->getCountry($telegramBot->getCountryCode()),
                countryPassed: true,
                locale: $telegramBot->getLocaleCode() === null ? null : $this->localeProvider->getLocale($telegramBot->getLocaleCode()),
                localePassed: true,
                checkUpdates: $telegramBot->checkUpdates(),
                checkUpdatesPassed: true,
                checkRequests: $telegramBot->checkRequests(),
                checkRequestsPassed: true,
                acceptPayments: $telegramBot->acceptPayments(),
                acceptPaymentsPassed: true,
                adminOnly: $telegramBot->adminOnly(),
                adminOnlyPassed: true,
                adminIds: $telegramBot->getAdminIds(),
                adminIdsPassed: true,
                primary: $telegramBot->primary(),
                primaryPassed: true,
            );
            $this->telegramBotCreator->createTelegramBot($telegramBotTransfer);
            $affectedRows++;
        }

        return $affectedRows;
    }

    private function transferTelegramChannels(): int
    {
        $affectedRows = 0;
        foreach ($this->telegramChannelRepository->doctrine()->findAll() as $telegramChannel) {
            $telegramChannelTransfer = new TelegramChannelTransfer(
                username: $telegramChannel->getUsername(),
                group: $telegramChannel->getGroup(),
                groupPassed: true,
                name: $telegramChannel->getName(),
                namePassed: true,
                country: $telegramChannel->getCountryCode() === null ? null : $this->countryProvider->getCountry($telegramChannel->getCountryCode()),
                countryPassed: true,
                locale: $telegramChannel->getLocaleCode() === null ? null : $this->localeProvider->getLocale($telegramChannel->getLocaleCode()),
                localePassed: true,
                level1Region: $telegramChannel->getLevel1RegionId() === null ? null : $this->level1RegionProvider->getLevel1Region($telegramChannel->getLevel1RegionId()),
                level1RegionPassed: true,
                chatId: $telegramChannel->getChatId(),
                chatIdPassed: true,
                primary: $telegramChannel->primary(),
                primaryPassed: true,
            );
            $this->telegramChannelCreator->createTelegramChannel($telegramChannelTransfer);
            $affectedRows++;
        }

        return $affectedRows;
    }
}