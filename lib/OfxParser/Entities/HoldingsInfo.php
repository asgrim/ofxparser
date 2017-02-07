<?php

namespace OfxParser\Entities;

class HoldingsInfo extends AbstractEntity
{
	private static $types = [
        'OTHER' => 'Other',
    ];

    /**
     * @var string
     */
    public $ticker;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $secid;

    /**
     * @var string
     */
    public $yield;
}
