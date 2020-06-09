<?php
namespace App\Command;

use App\MoneyMoney\MoneyMoney;
use App\Ynab\YnabApi;
use App\Ynab\YnabApiFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class SetupCommand extends Command
{
    protected static $defaultName = 'app:setup';
    private string $projectDir;

    private MoneyMoney $moneyMoney;

    private Filesystem $filesystem;

    private YnabApiFactory $ynabApiFactory;

    public function __construct(MoneyMoney $moneyMoney, Filesystem $filesystem, YnabApiFactory $ynabApiFactory)
    {
        parent::__construct();
        $this->moneyMoney = $moneyMoney;
        $this->filesystem = $filesystem;
        $this->ynabApiFactory = $ynabApiFactory;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'path to config', posix_getpwuid(posix_getuid())['dir'].'/.config/ynab_sync/config.json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $configFile = $input->getOption('config');
        $config = [];
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
        } else {
            $io->writeln('No config found. Creating new one');
        }

        // Configure API key authorization: bearer
        $budgets = [];
        /** @var YnabApi $api */
        $api = null;

        $token = $io->ask('YNAB Token', $config['ynab-token'] ?? null, function ($token) use (&$api, &$budgets) {
            $api = $this->ynabApiFactory->create($token);
            foreach ($api->getBudgets() as $budget) {
                $budgets[$budget->getId()] = $budget->getName();
            }

            return $token;
        });
        $config['ynab-token'] = $token;
        $config['budget'] = $io->choice('Budget', $budgets, $config['budget'] ?? null);

        $accounts = $this->moneyMoney->getAccounts();
        $accountList = [];
        $accountIds = [];
        foreach ($accounts as $account) {
            if ($account->isGroup()) {
                continue;
            }
            $accountIds[] = $account->getUuid();
            $accountList[] = $account->getName();
        }
        $ynabAccounts = $api->getAccounts($config['budget']);
        $ynabAccountsList = [];
        foreach ($ynabAccounts as $account) {
            $ynabAccountsList[$account->getId()] = $account->getName();
        }
        $io->section('Accounts to sync');
        $mapping = $config['mapping'] ?? [];
        foreach ($accounts as $account) {
            if ($account->isGroup()) {
                continue;
            }
            $value = $mapping[$account->getUuid()] ?? null;
            $sync = $io->confirm('Sync account '.$account->getName(), false !== $value);
            if (!$sync) {
                $mapping[$account->getUuid()] = false;
                continue;
            }
            $syncAccount = $io->choice('YNAB Account', $ynabAccountsList, $value);
            $mapping[$account->getUuid()] = $syncAccount;
            unset($ynabAccountsList[$syncAccount]);
        }
        $config['mapping'] = $mapping;

        $this->filesystem->dumpFile($configFile, json_encode($config, JSON_PRETTY_PRINT));

        return 0;
    }
}
