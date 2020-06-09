<?php
namespace App\Command;

use App\MoneyMoney\MoneyMoney;
use App\Syncer;
use App\Ynab\YnabApiFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncCommand extends Command
{
    protected static $defaultName = 'app:sync';
    /**
     * @var MoneyMoney
     */
    private $moneyMoney;

    private YnabApiFactory $ynabApiFactory;

    private Syncer $syncer;

    public function __construct(Syncer $syncer)
    {
        parent::__construct();
        $this->syncer = $syncer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sync money money to ynab')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'path to config', posix_getpwuid(posix_getuid())['dir'].'/.config/ynab_sync/config.json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = json_decode(file_get_contents($input->getOption('config')), true);
        $this->syncer->sync($config);

        return 0;
    }
}
