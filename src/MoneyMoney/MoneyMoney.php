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
class MoneyMoney
{
    /**
     * @var Serializer
     */
    private $serializer;

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
        $result = $this->runScript("tell application \"MoneyMoney\" to export transactions from account \"{$account->getUuid()}\" from date \"{$from->format('Y-m-d')}\" as \"plist\"");

        return $this->serializer->denormalize($result['transactions'], Transaction::class.'[]', 'xml', [DateTimeNormalizer::FORMAT_KEY => 'U']);
    }

    protected function runScript(string $command)
    {
        $process = new Process(['osascript', '-e', $command]);
        $process->mustRun();

        $plist = new CFPropertyList();
        $plist->parse($process->getOutput());

        return $plist->toArray();
    }
}
