<?php
namespace App\Ynab\Model;

/**
 * Class Account
 */
class Account
{
    private string $id;
    private string $name;
    private string $type;
    private float $balance;

    public function __construct(string $id, string $name, string $type, float $balance)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->balance = $balance / 1000;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }
}