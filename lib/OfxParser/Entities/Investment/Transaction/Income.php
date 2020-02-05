<?php

namespace OfxParser\Entities\Investment\Transaction;

use SimpleXMLElement;
use OfxParser\Entities\AbstractEntity;
use OfxParser\Entities\Investment;
use OfxParser\Entities\Investment\Transaction\Traits\IncomeType;
use OfxParser\Entities\Investment\Transaction\Traits\InvTran;
use OfxParser\Entities\Investment\Transaction\Traits\Pricing;
use OfxParser\Entities\Investment\Transaction\Traits\SecId;

/**
 * OFX 203 doc:
 * 13.9.2.4.3 Investment Buy/Sell Aggregates <INVBUY>/<INVSELL>
 *
 * Required:
 * <INVTRAN> aggregate
 * <SECID> aggregate
 * <INCOMETYPE>
 * <TOTAL>
 * <SUBACCTSEC>
 * <SUBACCTFUND>
 *
 * Optional:
 * ...many...
 *
 * Partial implementation.
 */
class Income extends Investment
{
    /**
     * Traits used to define properties
     */
    use IncomeType;
    use InvTran;
    use Pricing; // Not all of these are required for this node
    use SecId;

    /**
     * @var string
     */
    public $nodeName = 'INCOME';

    /**
     * Imports the OFX data for this node.
     * @param SimpleXMLElement $node
     * @return $this
     */
    public function loadOfx(SimpleXMLElement $node)
    {
        // Transaction data is in the root
        $this->loadInvTran($node->INVTRAN)
            ->loadSecId($node->SECID)
            ->loadPricing($node)
            ->loadIncomeType($node);

        return $this;
    }
}
