<?php

namespace OfxParser\Entities;

class BankAccount extends AbstractEntity
{
    /**
     * @var string
     */
    public $accountNumber;

    /**
     * @var string
     */
    public $accountType;

    /**
     * @var string
     */
    public $balance;

    /**
     * @var \DateTimeInterface
     */
    public $balanceDate;

    /**
     * @var string
     */
    public $routingNumber;

    /**
     * @var Statement
     */
    public $statement;

    /**
     * @var string
     */
    public $transactionUid;

    /**
     * @var string
     */
    public $agencyNumber;
}
