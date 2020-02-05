<?php

namespace OfxParser\Entities\Investment\Transaction\Traits;

use SimpleXMLElement;

trait IncomeType
{
    /**
     * Type of investment income:
     * CGLONG (capital gains-long term),
     * CGSHORT (capital gains-short term),
     * DIV (dividend),
     * INTEREST,
     * MISC
     * @var string
     */
    public $incomeType;

    /**
     * @param SimpleXMLElement $node
     * @return $this for chaining
     */
    protected function loadIncomeType(SimpleXMLElement $node)
    {
        $this->incomeType = (string) $node->INCOMETYPE;

        return $this;
    }
}
