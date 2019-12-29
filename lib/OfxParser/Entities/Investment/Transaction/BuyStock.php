<?php

namespace OfxParser\Entities\Investment\Transaction;

use SimpleXMLElement;
use OfxParser\Entities\Investment\Transaction\Traits\BuyType;

/**
 * OFX 203 doc:
 * 13.9.2.4.3 Investment Buy/Sell Aggregates <INVBUY>/<INVSELL>
 *
 * Properties found in the <INVBUY> aggregate,
 * plus <BUYTYPE> property.
 */
class BuyStock extends BuySecurity
{
    /**
     * Traits used to define properties
     */
    use BuyType;

    /**
     * @var string
     */
    public $nodeName = 'BUYSTOCK';

    /**
     * Imports the OFX data for this node.
     * @param SimpleXMLElement $node
     * @return $this
     */
    public function loadOfx(SimpleXMLElement $node)
    {
        parent::loadOfx($node);
        $this->loadBuyType($node);

        return $this;
    }
}

