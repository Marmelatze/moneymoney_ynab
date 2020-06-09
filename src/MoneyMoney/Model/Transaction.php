<?php
namespace App\MoneyMoney\Model;

/**
 * Class Transaction
 */
class Transaction
{
    protected int $id;
    protected string $accountNumber;
    protected string $accountUuid;
    protected float $amount;
    protected string $bankCode;
    protected bool $booked;
    protected \DateTimeInterface $bookingDate;
    protected string $bookingText;
    protected string $category;
    protected int $categoryId;
    protected string $categoryUuid;
    protected bool $checkmark;
    protected string $creditorId;
    protected string $currency;
    protected string $endToEndReference;
    protected string $mandateReference;
    protected string $name;
    protected ?string $purpose = null;
    protected \DateTimeInterface $valueDate;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber(string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string
     */
    public function getAccountUuid(): string
    {
        return $this->accountUuid;
    }

    /**
     * @param string $accountUuid
     */
    public function setAccountUuid(string $accountUuid): void
    {
        $this->accountUuid = $accountUuid;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    /**
     * @param string $bankCode
     */
    public function setBankCode(string $bankCode): void
    {
        $this->bankCode = $bankCode;
    }

    /**
     * @return bool
     */
    public function isBooked(): bool
    {
        return $this->booked;
    }

    /**
     * @param bool $booked
     */
    public function setBooked(bool $booked): void
    {
        $this->booked = $booked;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBookingDate(): \DateTimeInterface
    {
        return $this->bookingDate;
    }

    /**
     * @param \DateTimeInterface $bookingDate
     */
    public function setBookingDate(\DateTimeInterface $bookingDate): void
    {
        $this->bookingDate = $bookingDate;
    }

    /**
     * @return string
     */
    public function getBookingText(): string
    {
        return $this->bookingText;
    }

    /**
     * @param string $bookingText
     */
    public function setBookingText(string $bookingText): void
    {
        $this->bookingText = $bookingText;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     */
    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return string
     */
    public function getCategoryUuid(): string
    {
        return $this->categoryUuid;
    }

    /**
     * @param string $categoryUuid
     */
    public function setCategoryUuid(string $categoryUuid): void
    {
        $this->categoryUuid = $categoryUuid;
    }

    /**
     * @return bool
     */
    public function isCheckmark(): bool
    {
        return $this->checkmark;
    }

    /**
     * @param bool $checkmark
     */
    public function setCheckmark(bool $checkmark): void
    {
        $this->checkmark = $checkmark;
    }

    /**
     * @return string
     */
    public function getCreditorId(): string
    {
        return $this->creditorId;
    }

    /**
     * @param string $creditorId
     */
    public function setCreditorId(string $creditorId): void
    {
        $this->creditorId = $creditorId;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getEndToEndReference(): string
    {
        return $this->endToEndReference;
    }

    /**
     * @param string $endToEndReference
     */
    public function setEndToEndReference(string $endToEndReference): void
    {
        $this->endToEndReference = $endToEndReference;
    }

    /**
     * @return string
     */
    public function getMandateReference(): string
    {
        return $this->mandateReference;
    }

    /**
     * @param string $mandateReference
     */
    public function setMandateReference(string $mandateReference): void
    {
        $this->mandateReference = $mandateReference;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(?string $purpose): void
    {
        $this->purpose = $purpose;
    }

    public function getValueDate(): \DateTimeInterface
    {
        return $this->valueDate;
    }

    public function setValueDate(\DateTimeInterface $valueDate): void
    {
        $this->valueDate = $valueDate;
    }
}