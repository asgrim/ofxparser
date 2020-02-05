<?php

namespace OfxParser\Entities\Investment\Transaction;

use SimpleXMLElement;
use OfxParser\Entities\AbstractEntity;
use OfxParser\Entities\Investment;
use OfxParser\Entities\Investment\Transaction\Traits\InvTran;
use OfxParser\Entities\Investment\Transaction\Traits\SecId;
use OfxParser\Entities\Investment\Transaction\Traits\Pricing;

/**
 * OFX 203 doc:
 * 13.9.2.4.3 Investment Buy/Sell Aggregates <INVBUY>/<INVSELL>
 *
 * Properties found in the <INVSELL> aggregate.
 * Used for "other securities" SELL activities and provides the 
 * base properties to extend for more specific activities.
 *
 * Required:
 * <INVTRAN> aggregate
 * <SECID> aggregate
 * <UNITS>
 * <UNITPRICE>
 * <TOTAL>
 * <SUBACCTSEC>
 * <SUBACCTFUND>
 *
 * Optional:
 * ...many...
 *
 * Partial implementation.
 */
class SellSecurity extends Investment
{
    /**
     * Traits used to define properties
     */
    use InvTran;
    use SecId;
    use Pricing;

    /**
     * @var string
     */
    public $nodeName = 'SELLOTHER';

    /**
     * Imports the OFX data for this node.
     * @param SimpleXMLElement $node
     * @return $this
     */
    public function loadOfx(SimpleXMLElement $node)
    {
        // Transaction data is nested within <INVBUY> child node
        $this->loadInvTran($node->INVSELL->INVTRAN)
            ->loadSecId($node->INVSELL->SECID)
            ->loadPricing($node->INVSELL);

        return $this;
    }
}

