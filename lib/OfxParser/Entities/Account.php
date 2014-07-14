<?php

namespace OfxParser\Entities;

class Account extends AbstractEntity
{
    public $number;
    public $sortCode;
    public $statement;
    public $transactionUid;
}