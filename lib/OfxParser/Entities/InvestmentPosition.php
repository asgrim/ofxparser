<?php

namespace OfxParser\Entities;

class InvestmentPosition extends AbstractEntity
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
     * @var float
     */
    public $total;

    /**
     * @var float
     */
    public $date_value;

    /**
     * @var string
     */
    public $memo;

    /**
     * @var string
     */
    public $units;

    /**
     * @var string
     */
    public $unitprice;

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
