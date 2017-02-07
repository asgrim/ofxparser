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
    public $dateValue;

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
    public $unitPrice;

    /**
     * @var string
     */
    public $secid;

}
