<?php

namespace OfxParser\Entities;

class Transaction extends AbstractEntity
{
    private static $types = [
        'CREDIT' => 'Generic credit',
        'DEBIT' => 'Generic debit',
        'INT' => 'Interest earned or paid',
        'DIV' => 'Dividend',
        'FEE' => 'FI fee',
        'SRVCHG' => 'Service charge',
        'DEP' => 'Deposit',
        'ATM' => 'ATM debit or credit',
        'POS' => 'Point of sale debit or credit',
        'XFER' => 'Transfer',
        'CHECK' => 'Cheque',
        'PAYMENT' => 'Electronic payment',
        'CASH' => 'Cash withdrawal',
        'DIRECTDEP' => 'Direct deposit',
        'DIRECTDEBIT' => 'Merchant initiated debit',
        'REPEATPMT' => 'Repeating payment/standing order',
        'OTHER' => 'Other',
    ];

    /**
     * @var string
     */
    public $type;

    /**
     * Date the transaction was posted
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * Date the user initiated the transaction, if known
     * @var \DateTimeInterface|null
     */
    public $userInitiatedDate;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $uniqueId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $memo;

    /**
     * @var string
     */
    public $sic;

    /**
     * @var string
     */
    public $checkNumber;

    /**
     * Get the associated type description
     *
     * @return string
     */
    public function typeDesc()
    {
        // Cast SimpleXMLObject to string
        $type = (string)$this->type;
        return array_key_exists($type, self::$types) ? self::$types[$type] : '';
    }
}
