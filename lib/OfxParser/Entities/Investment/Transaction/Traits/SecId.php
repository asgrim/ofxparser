<?php

namespace OfxParser\Entities\Investment\Transaction\Traits;

use SimpleXMLElement;

/**
 * OFX 203 doc:
 * 13.8.1 Security Identification <SECID>
 */
trait SecId
{
    /**
     * Identifier for the security being traded.
     * @var string
     */
    public $securityId;

    /**
     * The type of identifier for the security being traded.
     * @var string
     */
    public $securityIdType;

    /**
     * @param SimpleXMLElement $node
     * @return $this for chaining
     */
    protected function loadSecId(SimpleXMLElement $node)
    {
        // <SECID>
        //  - REQUIRED: <UNIQUEID>, <UNIQUEIDTYPE>
        $this->securityId = (string) $node->UNIQUEID;
        $this->securityIdType = (string) $node->UNIQUEIDTYPE;

        return $this;
    }
}
