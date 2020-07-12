<?php
namespace App\Ynab;

use App\Ynab\Model\Account;
use App\Ynab\Model\Budget;
use App\Ynab\Model\Payee;
use App\Ynab\Model\Transaction;
use GuzzleHttp\Client;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;

/**
 * Class YnabApi.
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return Budget[]
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return Account[]
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return Transaction[]
     */
    public function getTransactions(string $budget, \DateTimeInterface $since = null)
    {
        $result = $this->httpClient->request('GET', "budgets/{$budget}/transactions", [
            'query' => [
                'since_date' => null !== $since ? $since->format('Y-m-d') : null,
            ],
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
        $json = $this->serializer->normalize($transaction, 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'
        ]);
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
        $json = $this->serializer->normalize($transactions, 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'
        ]);
        dump($json);
        $result = $this->httpClient->request(
            'POST',
            "budgets/{$budget}/transactions",
            [
                'json' => ['transactions' => $json],
            ]
        );
        dump(json_decode($result->getBody()->getContents()), true);
    }

    public function updateTransactions(string $budget, array $transactions)
    {
        $json = $this->serializer->normalize($transactions, 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'
        ]);
        $result = $this->httpClient->request(
            'PATCH',
            "budgets/{$budget}/transactions",
            [
                'json' => ['transactions' => $json],
            ]
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return Payee[]
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
