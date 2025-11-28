<?php

declare(strict_types=1);

namespace App\Command\Dynamodb;

use App\Repository\Telegram\Bot\TelegramBotRepository;
use App\Service\Intl\CountryProvider;
use App\Service\Intl\LocaleProvider;
use App\Service\Telegram\Bot\TelegramBotCreator;
use App\Transfer\Telegram\TelegramBotTransfer;
use Doctrine\ORM\EntityManagerInterface;
use OA\Dynamodb\ODM\EntityManager;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityManager $ddbEntityManager,
        private readonly TelegramBotRepository $telegramBotRepository,
        private TelegramBotCreator $telegramBotCreator,
        private readonly CountryProvider $countryProvider,
        private readonly LocaleProvider $localeProvider,
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

        $stats = [
            'telegram_bots' => $this->transferTelegramBots(),
        ];

        $this->ddbEntityManager->flush();

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

    private function transferTelegramBots(): int
    {
        $affectedRows = 0;
        foreach ($this->telegramBotRepository->findAll() as $telegramBot) {
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
}