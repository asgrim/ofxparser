<?php

namespace OfxParser\Entities\Investment;

use OfxParser\Entities\Investment;

class Account extends Investment
{
    /**
     * @var string
     */
    public $accountNumber;

    /**
     * @var string
     */
    public $brokerId;

    /**
     * @var Statement
     */
    public $statement;

    /**
     * @var string
     */
    public $transactionUid;

}
