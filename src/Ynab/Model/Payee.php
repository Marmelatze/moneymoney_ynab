<?php
namespace App\Ynab\Model;

/**
 * Class Payee
 */
class Payee
{
    private string $id;
    private string $name;
    private ?string $transferAccountId;
    private bool $deleted;

    public function __construct(string $id, string $name, ?string $transferAccountId, bool $deleted)
    {
        $this->id = $id;
        $this->name = $name;
        $this->transferAccountId = $transferAccountId;
        $this->deleted = $deleted;
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
     * @return string|null
     */
    public function getTransferAccountId(): ?string
    {
        return $this->transferAccountId;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
}