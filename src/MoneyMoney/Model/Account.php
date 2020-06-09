<?php
namespace App\MoneyMoney\Model;

/**
 * Class Account
 */
class Account
{
    private string $uuid;
    private string $name;
    /** @var AccountBalance[] */
    private array $balance = [];
    private bool $group;
    private bool $portfolio;

    public function __construct(string $uuid, string $name, array $balance, bool $group, bool $portfolio)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        foreach ($balance as $value) {
            $this->balance[] = new AccountBalance($value[0], $value[1]);
        }
        $this->group = $group;
        $this->portfolio = $portfolio;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return AccountBalance|null
     */
    public function getBalance(): ?AccountBalance
    {
        return current($this->balance);
    }

    /**
     * @return bool
     */
    public function isGroup(): bool
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    public function isPortfolio(): bool
    {
        return $this->portfolio;
    }
}