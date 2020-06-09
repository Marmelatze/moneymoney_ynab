<?php
namespace App;

use App\MoneyMoney\Model\Account;
use App\MoneyMoney\MoneyMoney;
use App\Ynab\Model\Payee;
use App\Ynab\Model\Transaction;
use App\Ynab\YnabApiFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Syncer
 */
class Syncer
{
    const IMPORT_PREFIX = "MM:";
    private LoggerInterface $logger;
    private MoneyMoney $moneyMoney;
    private YnabApiFactory $ynabApiFactory;
    private Ynab\YnabApi $api;
    /**
     * @var mixed
     */
    private string $budget;
    /**
     * @var Payee[]
     */
    private array $payees = [];
    private array $ynabTransactions = [];

    public function __construct(LoggerInterface $logger, MoneyMoney $moneyMoney, YnabApiFactory $ynabApiFactory)
    {
        $this->logger = $logger;
        $this->moneyMoney = $moneyMoney;
        $this->ynabApiFactory = $ynabApiFactory;
    }

    public function sync(array $config)
    {
        $this->api = $this->ynabApiFactory->create($config['ynab-token']);
        $this->budget = $config['budget'];
        $accounts = $this->moneyMoney->getAccounts();
        /** @var Account[] $moneyMoneyAccountMap */
        $moneyMoneyAccountMap = [];
        foreach ($accounts as $account) {
            $moneyMoneyAccountMap[$account->getUuid()] = $account;
        }

        $ynabAccounts = $this->api->getAccounts($config['budget']);
        /** @var \App\Ynab\Model\Account[] $ynabAccountMap */
        $ynabAccountMap = [];
        foreach ($ynabAccounts as $account) {
            $ynabAccountMap[$account->getId()] = $account;
        }

        $ynabTransactions = $this->api->getTransactions($this->budget, new \DateTime('-4days'));
        foreach ($ynabTransactions as $transaction) {
            $id = $transaction->getImportId();
            if (null === $id || substr($id, 0, strlen(self::IMPORT_PREFIX)) !== self::IMPORT_PREFIX) {
                $id = $transaction->getDate()->format('Y-m-d').'|'.$transaction->getPayeeName().'|'.$transaction->getMemo().'|'.round($transaction->getAmount()/1000,2);
            }
            $this->ynabTransactions[$transaction->getAccountId()][$id] = $transaction;
        }
        $this->logger->info('Syncing');

        $mapping = $config['mapping'];
        foreach ($mapping as $source => $target) {
            if (false === $target) {
                continue;
            }
            $sourceAccount = $moneyMoneyAccountMap[$source];
            $targetAccount = $ynabAccountMap[$target];
            $this->doSync($sourceAccount, $targetAccount);
        }
    }

    private function doSync(Account $sourceAccount, Ynab\Model\Account $targetAccount)
    {
        $this->logger->info('Syncing '.$sourceAccount->getName().' => '.$targetAccount->getName());
        if ($sourceAccount->isPortfolio()) {
            $this->syncAmount($sourceAccount, $targetAccount);
            return;
        }
        $bulkTransactions = [];
        $transactions = $this->moneyMoney->getTransactions($sourceAccount, new \DateTime('-3days'));
        foreach ($transactions as $transaction) {
            if (!$transaction->isBooked()) {
                continue;
            }
            $ynabTransaction = null;
            // find by import ID
            if (isset($this->ynabTransactions[$targetAccount->getId()][self::IMPORT_PREFIX.$transaction->getId()])) {
                $ynabTransaction = $this->ynabTransactions[$targetAccount->getId()][self::IMPORT_PREFIX.$transaction->getId()];
            }
            if (null === $ynabTransaction) {
                $id = $transaction->getValueDate()->format('Y-m-d').'|'.$transaction->getName().'|'.$transaction->getPurpose().'|'.$transaction->getAmount();
                if (isset($this->ynabTransactions[$targetAccount->getId()][$id])) {
                    $ynabTransaction = $this->ynabTransactions[$targetAccount->getId()][$id];
                }
            }
            if (null !== $ynabTransaction) {
                continue;
            }
            $this->logger->info('Creating transaction '. $transaction->getName().' ' . $transaction->getPurpose(). ' ('.$transaction->getAmount().')');
            $ynabTransaction = new Transaction($targetAccount->getId(), $transaction->getValueDate(), $transaction->getAmount() * 1000);
            $ynabTransaction->setImportId(self::IMPORT_PREFIX.$transaction->getId());
            $ynabTransaction->setPayeeName($transaction->getName());
            $ynabTransaction->setMemo($transaction->getPurpose());
            $ynabTransaction->setCleared("cleared");

            if (substr($ynabTransaction->getPayeeName(), 0, 6) == "PayPal" && preg_match('/Ihr Einkauf bei (.*?)$/si', $ynabTransaction->getMemo(), $matches)) {
                $ynabTransaction->setPayeeName($matches[1]);
                $ynabTransaction->setMemo(null);
            }

            $bulkTransactions[] = $ynabTransaction;
        }
        if (!empty($bulkTransactions)) {
            $this->api->newTransactions($this->budget, $bulkTransactions);
        }
    }

    private function syncAmount(Account $sourceAccount, Ynab\Model\Account $targetAccount)
    {
        if ($sourceAccount->getBalance()->getAmount() === $targetAccount->getBalance()) {
            return;
        }
        $this->logger->info(
            'Updating balance from '.$targetAccount->getBalance().' to '.$sourceAccount->getBalance()->getAmount()
        );
        $this->api->getPayees($this->budget);
        $transaction = new Transaction(
            $targetAccount->getId(),
            new \DateTime(),
            round(($sourceAccount->getBalance()->getAmount() - $targetAccount->getBalance())*1000, 0)
        );
        $transaction->setPayeeId($this->getPayee("Manual Balance Adjustment")->getId());
        $transaction->setApproved(true);
        $this->api->newTransaction($this->budget, $transaction);
    }

    private function getPayee(string $name): ?Payee
    {
        if (empty($this->payees)) {
            foreach($this->api->getPayees($this->budget) as $payee) {
                $this->payees[$payee->getName()] = $payee;
            }
        }

        return $this->payees[$name] ?? null;
    }

}