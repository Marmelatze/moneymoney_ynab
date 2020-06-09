<?php
namespace App\Ynab\Model;

/**
 * Class Transaction
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

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->accountId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param string|null $payeeName
     */
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

    /**
     * @return string|null
     */
    public function getPayeeId(): ?string
    {
        return $this->payeeId;
    }

    /**
     * @param string|null $payeeId
     */
    public function setPayeeId(?string $payeeId): void
    {
        $this->payeeId = $payeeId;
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->approved;
    }

    /**
     * @param bool $approved
     */
    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }

    /**
     * @return string|null
     */
    public function getMemo(): ?string
    {
        return $this->memo;
    }

    /**
     * @param string|null $memo
     */
    public function setMemo(?string $memo): void
    {
        $this->memo = $memo;
    }

    /**
     * @return string|null
     */
    public function getImportId(): ?string
    {
        return $this->importId;
    }

    /**
     * @param string|null $importId
     */
    public function setImportId(?string $importId): void
    {
        $this->importId = $importId;
    }

    /**
     * @return string|null
     */
    public function getCleared(): ?string
    {
        return $this->cleared;
    }

    /**
     * @param string|null $cleared
     */
    public function setCleared(?string $cleared): void
    {
        $this->cleared = $cleared;
    }
}