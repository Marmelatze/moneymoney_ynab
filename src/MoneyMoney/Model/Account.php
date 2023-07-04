<?php

namespace App\MoneyMoney\Model;

/**
 * Class Account.
 */
class Account
{
    private string $uuid;
    private string $name;
    /** @var AccountBalance[] */
    private array $balance = [];
    private bool $group;
    private bool $portfolio;
    private array $attributes = [];

    /**
     * Account constructor.
     */
    public function __construct(string $uuid, string $name, array $balance, bool $group, bool $portfolio, array $attributes)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        foreach ($balance as $value) {
            $this->balance[] = new AccountBalance($value[0], $value[1]);
        }
        $this->group = $group;
        $this->portfolio = $portfolio;
        $this->attributes = $attributes;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBalance()
    {
        return current($this->balance);
    }

    public function isGroup(): bool
    {
        return $this->group;
    }

    public function isPortfolio(): bool
    {
        return $this->portfolio;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getYnabAccount(): ?string
    {
        if (!isset($this->attributes['YNAB_ACCOUNT'])) {
            return null;
        }

        return $this->attributes['YNAB_ACCOUNT'];
    }
}
