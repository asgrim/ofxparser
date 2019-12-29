<?php

namespace OfxParser\Entities\Investment\Transaction;

/**
 * OFX 203 doc:
 * 13.9.2.4.3 Investment Buy/Sell Aggregates <INVBUY>/<INVSELL>
 *
 * <SELLSTOCK>, plus <RELFITID> property.
 */
class SellMutualFund extends SellStock
{
    /**
     * @var string
     */
    public $nodeName = 'SELLMF';

    /**
     * RELFITID used to relate transactions associated with mutual fund exchanges.
     * @var string
     */
    public $relatedUniqueId;
}

