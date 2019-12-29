<?php

namespace OfxParser\Entities\Investment\Transaction\Traits;

use SimpleXMLElement;

/**
 * Combo for units, price, and total
 */
trait Pricing
{
    /**
     * @var float
     */
    public $units;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var float
     */
    public $total;

    /**
     * Where did the money for the transaction come from or go to?
     * CASH, MARGIN, SHORT, OTHER
     * @var string
     */
    public $subAccountFund;

    /**
     * Sub-account type for the security:
     * CASH, MARGIN, SHORT, OTHER
     * @var string
     */
    public $subAccountSec;

    /**
     * @param SimpleXMLElement $node
     * @return $this for chaining
     */
    protected function loadPricing(SimpleXMLElement $node)
    {
        $this->units = (string) $node->UNITS;
        $this->unitPrice = (string) $node->UNITPRICE;
        $this->total = (string) $node->TOTAL;
        $this->subAccountFund = (string) $node->SUBACCTFUND;
        $this->subAccountSec = (string) $node->SUBACCTSEC;

        return $this;
    }
}
