<?php

namespace App;

use App\MoneyMoney\Model\Account;
use App\MoneyMoney\MoneyMoneyApi;
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
    public const IMPORT_PREFIX = 'MM';
    public const LOOKUP_NAMES = ['PayPal', 'Amzn', 'Amazon'];
    private LoggerInterface $logger;
    private MoneyMoneyApi $moneyMoney;
    private YnabApiFactory $ynabApiFactory;
    private Ynab\YnabApi $api;
    private string $budget;
    /**
     * @var Payee[]
     */
    private array $payees = [];
    private array $ynabTransactions = [];
    private array $referenceAccounts = [];

    private TransactionMatcher $transactionMatcher;

    public function __construct(LoggerInterface $logger, MoneyMoneyApi $moneyMoney, YnabApiFactory $ynabApiFactory, TransactionMatcher $transactionMatcher)
    {
        $this->logger = $logger;
        $this->moneyMoney = $moneyMoney;
        $this->ynabApiFactory = $ynabApiFactory;
        $this->transactionMatcher = $transactionMatcher;
    }

    public function sync(array $config)
    {
        $this->api = $this->ynabApiFactory->create($config['ynab-token']);
        $this->budget = $config['budget'];
        $accounts = $this->moneyMoney->getAccounts();
        /** @var Account[] $moneyMoneyAccountMap */
        $moneyMoneyAccountMap = [];
        foreach ($accounts as $account) {
            if (null === $account->getYnabAccount()) {
                continue;
            }
            $moneyMoneyAccountMap[$account->getUuid()] = $account;
            if ('reference' === $account->getYnabAccount()) {
                $this->referenceAccounts[] = $moneyMoneyAccountMap[$account->getUuid()];
            }
        }

        $ynabAccounts = $this->api->getAccounts($config['budget']);
        /** @var \App\Ynab\Model\Account[] $ynabAccountMap */
        $ynabAccountMap = [];
        foreach ($ynabAccounts as $account) {
            if (isset($ynabAccountMap[$account->getName()])) {
                throw new \RuntimeException('YNAB account '.$account->getName().' is duplicate');
            }
            $ynabAccountMap[$account->getName()] = $account;
        }

        $ynabTransactions = $this->api->getTransactions($this->budget, new \DateTime('-90days'));
        foreach ($ynabTransactions as $transaction) {
            $id = $transaction->getImportId();
            if (null === $id) {
                continue;
            }
            $this->ynabTransactions[$transaction->getAccountId()][$id] = $transaction;
        }
        $this->logger->info('Syncing');
        foreach ($accounts as $account) {
            if (null === $account->getYnabAccount() || 'reference' === $account->getYnabAccount()) {
                continue;
            }
            if (!isset($ynabAccountMap[$account->getYnabAccount()])) {
                throw new \RuntimeException('YNAB account '.$account->getYnabAccount().' not found for Money Money account '.$account->getName());
            }
            $targetAccount = $ynabAccountMap[$account->getYnabAccount()];
            $this->doSync($account, $targetAccount);
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
        $transactions = $this->moneyMoney->getTransactions($sourceAccount, new \DateTime('-90days'));
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
                (int) ($transaction->getAmount() * 1000),
                $transaction->getValueDate()->format('Y-m-d'),
            ]);
            if (isset($this->ynabTransactions[$targetAccount->getId()][$id])) {
                $ynabTransaction = $this->ynabTransactions[$targetAccount->getId()][$id];
                if ($ynabTransaction->isApproved()) {
                    continue;
                }
            }
            if (null === $ynabTransaction) {
                $this->logger->info('Creating transaction '.$transaction->getValueDate()->format('Y-m-d').' '.$transaction->getName().' '.$transaction->getPurpose().' ('.$transaction->getAmount().'|'.$transaction->getId());
                $ynabTransaction = new Transaction($targetAccount->getId(), $transaction->getValueDate(), (int) ($transaction->getAmount() * 1000));
            }
            $ynabTransaction->setImportId($id);
            $ynabTransaction->setPayeeName($transaction->getName());

            if (0 == mb_strlen($ynabTransaction->getMemo())) {
                $ynabTransaction->setMemo($transaction->getPurpose());
            }
            if (0 == mb_strlen($ynabTransaction->getMemo())) {
                $ynabTransaction->setMemo($transaction->getMandateReference().','.$transaction->getEndToEndReference());
            }
            foreach (self::LOOKUP_NAMES as $name) {
                if (str_starts_with($ynabTransaction->getPayeeName(), $name)) {
                    $this->lookupReference($transaction, $ynabTransaction);
                }
            }

            $ynabTransaction->setCleared('cleared');

            if (null === $ynabTransaction->getId()) {
                // $newTransactions[] = $ynabTransaction;
                //    $this->api->newTransaction($this->budget, $ynabTransaction);
                try {
                    $this->api->newTransaction($this->budget, $ynabTransaction);
                } catch (ClientException $e) {
                    if (409 == $e->getResponse()->getStatusCode()) {
                        $this->logger->info('Transation already exists. Updating');
                        // $updateTransactions[] = $ynabTransaction;
                    } else {
                        throw $e;
                    }
                }
            } else {
                $updateTransactions[] = $ynabTransaction;
            }
        }
        if (!empty($updateTransactions)) {
            $this->api->updateTransactions($this->budget, $updateTransactions);
        }
        // $ynabAccount = $this->api->getAccount($this->budget, $targetAccount->getId());
        // $this->syncAmount($sourceAccount, $ynabAccount);
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

    private function lookupReference(MoneyMoney\Model\Transaction $transaction, ?Transaction $ynabTransaction)
    {
        $this->transactionMatcher->match($this->referenceAccounts, $transaction, $ynabTransaction);
    }
}
