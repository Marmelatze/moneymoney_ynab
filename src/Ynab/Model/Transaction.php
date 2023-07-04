<?php

namespace App\Ynab\Model;

/**
 * Class Transaction.
 */
class Transaction
{
    private ?string $id = null;
    private string $accountId;
    private \DateTimeInterface $date;
    private int $amount;
    private ?string $payeeId = null;
    private ?string $payeeName = null;
    private bool $approved = false;
    private ?string $memo = null;
    private ?string $importId = null;
    private ?string $cleared = null;
    private bool $deleted = false;

    public function __construct(string $accountId, \DateTimeInterface $date, int $amount)
    {
        $this->accountId = $accountId;
        $this->date = $date;
        $this->amount = $amount;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setPayeeName(?string $payeeName): void
    {
        $this->payeeName = $payeeName;
    }

    /**
     * @return string
     */
    public function getPayeeName(): ?string
    {
        return $this->payeeName;
    }

    public function getPayeeId(): ?string
    {
        return $this->payeeId;
    }

    public function setPayeeId(?string $payeeId): void
    {
        $this->payeeId = $payeeId;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }

    public function getMemo(): ?string
    {
        return $this->memo;
    }

    public function setMemo(?string $memo): void
    {
        if (mb_strlen($memo) > 200) {
            $memo = mb_substr($memo, 0, 200);
        }
        $this->memo = $memo;
    }

    public function getImportId(): ?string
    {
        return $this->importId;
    }

    public function setImportId(?string $importId): void
    {
        $this->importId = $importId;
    }

    public function getCleared(): ?string
    {
        return $this->cleared;
    }

    public function setCleared(?string $cleared): void
    {
        $this->cleared = $cleared;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }
}
