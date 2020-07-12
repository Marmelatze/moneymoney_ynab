<?php
namespace App;

use App\MoneyMoney\Model\Account;
use App\MoneyMoney\MoneyMoney;
use App\Ynab\Model\Payee;
use App\Ynab\Model\Transaction;
use App\Ynab\YnabApiFactory;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;

/**
 * Class Syncer.
 */
class Syncer
{
    const IMPORT_PREFIX = 'MM';
    private LoggerInterface $logger;
    private MoneyMoney $moneyMoney;
    private YnabApiFactory $ynabApiFactory;
    private Ynab\YnabApi $api;
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

        $ynabTransactions = $this->api->getTransactions($this->budget, new \DateTime('-5days'));
        foreach ($ynabTransactions as $transaction) {
            $id = $transaction->getImportId();
            if (null === $id) {
                continue;
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
        $newTransactions = [];
        $transactions = $this->moneyMoney->getTransactions($sourceAccount, new \DateTime('-5days'));
        foreach ($transactions as $transaction) {
            if (!$transaction->isBooked()) {
                continue;
            }
            if ($transaction->getValueDate() >= new \DateTime('tomorrow')) {
                continue;
            }
            $ynabTransaction = null;
            // find by import ID
            $id = implode(':', [
                self::IMPORT_PREFIX,
                $transaction->getId(),
                (int)($transaction->getAmount()*1000),
                $transaction->getValueDate()->format('Y-m-d')
            ]);
            if (isset($this->ynabTransactions[$targetAccount->getId()][$id])) {
                $ynabTransaction = $this->ynabTransactions[$targetAccount->getId()][$id];
            }
            if (null === $ynabTransaction) {
                $this->logger->info('Creating transaction '.$transaction->getValueDate()->format('Y-m-d').' '.$transaction->getName().' '.$transaction->getPurpose().' ('.$transaction->getAmount().'|'.$transaction->getId());
                $ynabTransaction = new Transaction($targetAccount->getId(), $transaction->getValueDate(), (int) ($transaction->getAmount() * 1000));
            }
            $ynabTransaction->setImportId($id);
            $ynabTransaction->setPayeeName($transaction->getName());
            if (strlen($ynabTransaction->getMemo()) == 0) {
                $ynabTransaction->setMemo($transaction->getPurpose());
            }
            if (strlen($ynabTransaction->getMemo()) == 0) {
                $ynabTransaction->setMemo($transaction->getMandateReference().','.$transaction->getEndToEndReference());
            }
            $ynabTransaction->setCleared($transaction->isBooked() ? 'cleared' : 'uncleared');

            if ('PayPal' == mb_substr($ynabTransaction->getPayeeName(), 0, 6) && preg_match('/Ihr Einkauf bei (.*?)$/si', $ynabTransaction->getMemo(), $matches)) {
                $ynabTransaction->setPayeeName($matches[1]);
                $ynabTransaction->setMemo(null);
            }
            if (null === $ynabTransaction->getId()) {
                #$newTransactions[] = $ynabTransaction;
            #    $this->api->newTransaction($this->budget, $ynabTransaction);
                try {
                    $this->api->newTransaction($this->budget, $ynabTransaction);
                } catch (ClientException $e) {
                    if ($e->getResponse()->getStatusCode() == 409) {
                        $this->logger->info('Transation already exists. Updating');
                        $updateTransactions[] = $ynabTransaction;
                    } else {
                        throw $e;
                    }
                }

            } else {
                $updateTransactions[] = $ynabTransaction;
            }
        }
        if (!empty($newTransactions)) {
            $this->api->newTransactions($this->budget, $newTransactions);
        }
        if (!empty($updateTransactions)) {
        #    $this->api->updateTransactions($this->budget, $updateTransactions);
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
            (int) round(($sourceAccount->getBalance()->getAmount() - $targetAccount->getBalance()) * 1000, 0)
        );
        $transaction->setPayeeId($this->getPayee('Manual Balance Adjustment')->getId());
        $transaction->setApproved(true);
        $this->api->newTransaction($this->budget, $transaction);
    }

    private function getPayee(string $name): ?Payee
    {
        if (empty($this->payees)) {
            foreach ($this->api->getPayees($this->budget) as $payee) {
                $this->payees[$payee->getName()] = $payee;
            }
        }

        return $this->payees[$name] ?? null;
    }
}
