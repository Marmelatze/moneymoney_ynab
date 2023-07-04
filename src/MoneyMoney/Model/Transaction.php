<?php

namespace App\MoneyMoney\Model;

/**
 * Class Transaction.
 */
class Transaction
{
    protected int $id;
    protected string $accountNumber;
    protected string $accountUuid;
    protected float $amount;
    protected ?string $bankCode = null;
    protected bool $booked;
    protected \DateTimeInterface $bookingDate;
    protected string $bookingText;
    protected string $category;
    protected int $categoryId;
    protected string $categoryUuid;
    protected bool $checkmark;
    protected string $creditorId;
    protected string $currency;
    protected ?string $endToEndReference = null;
    protected ?string $mandateReference = null;
    protected string $name = '';
    protected ?string $purpose = null;
    protected \DateTimeInterface $valueDate;

    public function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    public function getAccountUuid(): string
    {
        return $this->accountUuid;
    }

    public function setAccountUuid(string $accountUuid): void
    {
        $this->accountUuid = $accountUuid;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getBankCode(): ?string
    {
        return $this->bankCode;
    }

    public function setBankCode(string $bankCode): void
    {
        $this->bankCode = $bankCode;
    }

    public function isBooked(): bool
    {
        return $this->booked;
    }

    public function setBooked(bool $booked): void
    {
        $this->booked = $booked;
    }

    public function getBookingDate(): \DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function setBookingDate(\DateTimeInterface $bookingDate): void
    {
        $this->bookingDate = $bookingDate;
    }

    public function getBookingText(): string
    {
        return $this->bookingText;
    }

    public function setBookingText(string $bookingText): void
    {
        $this->bookingText = $bookingText;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getCategoryUuid(): string
    {
        return $this->categoryUuid;
    }

    public function setCategoryUuid(string $categoryUuid): void
    {
        $this->categoryUuid = $categoryUuid;
    }

    public function isCheckmark(): bool
    {
        return $this->checkmark;
    }

    public function setCheckmark(bool $checkmark): void
    {
        $this->checkmark = $checkmark;
    }

    public function getCreditorId(): string
    {
        return $this->creditorId;
    }

    public function setCreditorId(string $creditorId): void
    {
        $this->creditorId = $creditorId;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getEndToEndReference(): ?string
    {
        return $this->endToEndReference;
    }

    public function setEndToEndReference(string $endToEndReference): void
    {
        $this->endToEndReference = $endToEndReference;
    }

    public function getMandateReference(): ?string
    {
        return $this->mandateReference;
    }

    public function setMandateReference(string $mandateReference): void
    {
        $this->mandateReference = $mandateReference;
    }

    public function getName(): string
    {
        return $this->name;
    }

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
