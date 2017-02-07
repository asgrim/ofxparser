<?php

namespace OfxParser\Entities;

class InvestmentTransaction extends AbstractEntity
{
    private static $types = [
        'CREDIT' => 'Generic credit',
        'DEBIT' => 'Generic debit',
        'BUY' => 'Stock Purchase',
        'INT' => 'Interest earned or paid',
        'DIV' => 'Dividend',
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
    public $memo;

    /**
     * @var string
     */
    public $secid;

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
