<?php

namespace OfxParser\Entities\Investment\Transaction\Traits;

use SimpleXMLElement;
use OfxParser\Utils;

/**
 * OFX 203 doc:
 * 13.9.2.4.1 General Transaction Aggregate <INVTRAN>
 *
 * Limited implementation
 */
trait InvTran
{
    /**
     * This is the unique identifier in the broker's system,
     * NOT to be confused with the UNIQUEID node for the security.
     * @var string
     */
    public $uniqueId;

    /**
     * Date the trade occurred
     * @var \DateTimeInterface
     */
    public $tradeDate;

    /**
     * Date the trade was settled
     * @var \DateTimeInterface
     */
    public $settlementDate;

    /**
     * Transaction memo, as provided from broker.
     * @var string
     */
    public $memo;

    /**
     * @param SimpleXMLElement $node
     * @return $this for chaining
     */
    protected function loadInvTran(SimpleXMLElement $node)
    {
        // <INVTRAN>
        //  - REQUIRED: <FITID>, <DTTRADE>
        //  - all others optional
        $this->uniqueId = (string) $node->FITID;
        $this->tradeDate = Utils::createDateTimeFromStr($node->DTTRADE);
        if (isset($node->DTSETTLE)) {
            $this->settlementDate = Utils::createDateTimeFromStr($node->DTSETTLE);
        }
        if (isset($node->MEMO)) {
            $this->memo = (string) $node->MEMO;
        }

        return $this;
    }
}
