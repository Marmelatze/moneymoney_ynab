<?php
namespace App\Ynab;

use App\Ynab\Model\Account;
use App\Ynab\Model\Budget;
use App\Ynab\Model\Payee;
use App\Ynab\Model\Transaction;
use GuzzleHttp\Client;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;

/**
 * Class YnabApi
 */
class YnabApi
{
    private Serializer $serializer;
    private Client $httpClient;

    public function __construct(Serializer $serializer, string $token)
    {
        $this->serializer = $serializer;
        $this->httpClient = new Client(
            [
                'base_uri' => 'https://api.youneedabudget.com/v1/',
                'headers' => ['Authorization' => 'Bearer '.$token],
            ]
        );
    }

    /**
     * @return Budget[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBudgets(): array
    {
        $result = $this->httpClient->request('GET', 'budgets');
        $content = $result->getBody()->getContents();
        dump(json_decode($content));

        return $this->serializer->deserialize(
            $content,
            Budget::class.'[]',
            'json',
            [UnwrappingDenormalizer::UNWRAP_PATH => '[data][budgets]']
        );
    }

    /**
     * @param string $budget
     *
     * @return Account[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccounts(string $budget)
    {
        $result = $this->httpClient->request('GET', "budgets/{$budget}/accounts");
        $content = $result->getBody()->getContents();

        return $this->serializer->deserialize(
            $content,
            Account::class.'[]',
            'json',
            [UnwrappingDenormalizer::UNWRAP_PATH => '[data][accounts]']
        );
    }

    /**
     * @return Transaction[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTransactions(string $budget, \DateTimeInterface $since = null)
    {
        $result = $this->httpClient->request('GET', "budgets/{$budget}/transactions", [
            'query' => [
                'since_date' => $since !== null ? $since->format('Y-m-d') : null,
            ]
        ]);
        $content = $result->getBody()->getContents();

        return $this->serializer->deserialize(
            $content,
            Transaction::class.'[]',
            'json',
            [UnwrappingDenormalizer::UNWRAP_PATH => '[data][transactions]']
        );
    }

    public function newTransaction(string $budget, Transaction $transaction)
    {
        $json = $this->serializer->normalize($transaction, 'json');
        $result = $this->httpClient->request(
            'POST',
            "budgets/{$budget}/transactions",
            [
                'json' => ['transaction' => $json],
            ]
        );
    }


    public function newTransactions(string $budget, array $transactions)
    {
        $json = $this->serializer->normalize($transactions, 'json');
        $result = $this->httpClient->request(
            'POST',
            "budgets/{$budget}/transactions",
            [
                'json' => ['transactions' => $json],
            ]
        );
    }

    /**
     * @param string $budget
     *
     * @return Payee[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPayees(string $budget)
    {
        $result = $this->httpClient->request('GET', "budgets/{$budget}/payees");
        $content = $result->getBody()->getContents();

        return $this->serializer->deserialize(
            $content,
            Payee::class.'[]',
            'json',
            [UnwrappingDenormalizer::UNWRAP_PATH => '[data][payees]']
        );
    }


}