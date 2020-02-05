<?php

namespace OfxParser\Entities\Investment\Transaction;

use SimpleXMLElement;
use OfxParser\Entities\Investment\Transaction\Traits\SellType;

/**
 * OFX 203 doc:
 * 13.9.2.4.3 Investment Buy/Sell Aggregates <INVBUY>/<INVSELL>
 *
 * Properties found in the <INVSELL> aggregate,
 * plus <SELLTYPE> property.
 */
class SellStock extends SellSecurity
{
    /**
     * Traits used to define properties
     */
    use SellType;

    /**
     * @var string
     */
    public $nodeName = 'SELLSTOCK';

    /**
     * Imports the OFX data for this node.
     * @param SimpleXMLElement $node
     * @return $this
     */
    public function loadOfx(SimpleXMLElement $node)
    {
        parent::loadOfx($node);
        $this->loadSellType($node);

        return $this;
    }
}

