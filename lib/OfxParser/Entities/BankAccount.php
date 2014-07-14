<?php

namespace OfxParser\Entities;

class BankAccount extends Account
{
    protected $types = array(
        'CHECKING',
        'SAVINGS',
        'MONEYMRKT',
        'CREDITLINE'
    );

    public $accountType;
    public $balance;
    public $balanceDate;
}