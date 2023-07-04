<?php

namespace App;

use App\MoneyMoney\Model\Transaction;
use App\MoneyMoney\MoneyMoneyApi;

class TransactionMatcher
{
    private string $matchFile;
    private ?array $matches = null;

    private MoneyMoneyApi $moneyMoney;

    /**
     * TransactionMatcher constructor.
     */
    public function __construct(string $matchFile, MoneyMoneyApi $moneyMoney)
    {
        if (str_starts_with($matchFile, '~')) {
            $matchFile = posix_getpwuid(posix_getuid())['dir'].mb_substr($matchFile, 1);
        }
        $this->matchFile = $matchFile;
        $this->moneyMoney = $moneyMoney;
    }

    protected function loadMatches(): array
    {
        if (null !== $this->matches) {
            return $this->matches;
        }

        if (!is_file($this->matchFile)) {
            return $this->matches = [];
        }

        return $this->matches = json_decode(file_get_contents($this->matchFile), true);
    }

    public function alreadyMatched(string|int $id): bool
    {
        return \in_array($id, $this->matches, true);
    }

    public function match(
        array $referenceAccounts,
        Transaction $transaction,
        ?Ynab\Model\Transaction $ynabTransaction
    ) {
        $inverseFind = false;
        $this->loadMatches();
        if (isset($this->matches[$transaction->getId()])) {
            return;
        }
        $amount = $transaction->getAmount();
        $matchedTransactions = $this->findTransactions($referenceAccounts, $amount);

        if (empty($matchedTransactions)) {
            // try to find inverse transaction
            $inverseFind = true;
            $matchedTransactions = $this->findTransactions($referenceAccounts, -1 * $amount);
            if (empty($matchedTransactions)) {
                return;
            }
        }
        rsort($matchedTransactions);
        reset($matchedTransactions);
        /** @var Transaction $matched */
        $matched = current($matchedTransactions);
        if ('PayPal' == $matched->getBankCode()) {
            $ynabTransaction->setPayeeName($matched->getName());
        }
        if ($inverseFind && str_contains($matched->getPurpose(), 'contra')) {
            $inverseMatch = $this->findInverseTransaction($matched);
            if (null !== $inverseMatch) {
                $matched = $inverseMatch;
            }
        }

        $ynabTransaction->setMemo($matched->getPurpose());
        $this->matches[$transaction->getId()] = $matched->getId();
    }

    protected function findTransactions(array $referenceAccounts, $amount): array
    {
        $matchedTransactions = [];
        foreach ($referenceAccounts as $account) {
            $transactions = $this->moneyMoney->getTransactions($account, new \DateTime('-7days'));
            foreach ($transactions as $transaction2) {
                if ($transaction2->getAmount() !== $amount) {
                    continue;
                }
                if ($this->alreadyMatched($transaction2->getId())) {
                    continue;
                }
                $matchedTransactions[] = $transaction2;
            }
        }

        return $matchedTransactions;
    }

    public function __destruct()
    {
        if (null !== $this->matches) {
            file_put_contents($this->matchFile, json_encode($this->matches));
        }
    }

    private function findInverseTransaction(Transaction $matched): ?Transaction
    {
        $transactions = $this->moneyMoney->getTransactionsByUUID($matched->getAccountUuid(), new \DateTime('-7days'));
        foreach ($transactions as $transaction) {
            if ($transaction->getName() === $matched->getName() && $transaction->getAmount() <= 0) {
                return $transaction;
            }
        }

        return null;
    }
}
