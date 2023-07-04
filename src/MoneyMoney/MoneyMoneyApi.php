<?php

namespace App\MoneyMoney;

use App\MoneyMoney\Model\Account;
use App\MoneyMoney\Model\Transaction;
use CFPropertyList\CFPropertyList;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * Class MoneyMoney.
 */
class MoneyMoneyApi
{
    /**
     * @var Serializer
     */
    private $serializer;
    private ?array $transactions = null;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return Account[]
     */
    public function getAccounts(): array
    {
        $result = $this->runScript('tell application "MoneyMoney" to export accounts');

        return $this->serializer->denormalize($result, Account::class.'[]');
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(Account $account, \DateTimeInterface $from): array
    {
        return $this->getTransactionsByUUID($account->getUuid(), $from);
    }

    /**
     * @return Transaction[]
     */
    public function getTransactionsByUUID(string $account, \DateTimeInterface $from): array
    {
        $list = [];
        foreach ($this->getAllTransactions($from) as $transaction) {
            if ($transaction['accountUuid'] === $account) {
                $list[] = $transaction;
            }
        }

        return $this->serializer->denormalize(
            $list,
            Transaction::class.'[]',
            'xml',
            [DateTimeNormalizer::FORMAT_KEY => 'U']
        );
    }

    protected function runScript(string $command)
    {
        $process = new Process(['/usr/bin/osascript', '-e', $command]);
        $process->mustRun();

        $plist = new CFPropertyList();
        $plist->parse($process->getOutput());

        return $plist->toArray();
    }

    private function getAllTransactions(\DateTimeInterface $from): array
    {
        if (null !== $this->transactions) {
            return $this->transactions;
        }

        $result = $this->runScript(
            "tell application \"MoneyMoney\" to export transactions from date \"{$from->format('Y-m-d')}\" as \"plist\""
        );

        return $this->transactions = $result['transactions'];
    }
}
