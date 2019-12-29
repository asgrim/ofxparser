<?php

namespace OfxParser\Entities;

use SimpleXMLElement;

abstract class Investment extends AbstractEntity implements Inspectable, OfxLoadable
{
    /**
     * Get a list of properties defined for this entity.
     *
     * Since Traits are being used for multiple inheritance,
     * it can be challenging to know which properties exist
     * in the entity. 
     *
     * @return array array('prop_name' => 'prop_name', ...)
     */
    public function getProperties()
    {
        $props = array_keys(get_object_vars($this));

        return array_combine($props, $props);
    }

    /**
     * All Investment entities require a loadOfx method.
     * @param SimpleXMLElement $node
     * @return $this For chaining
     * @throws \Exception
     */
    public function loadOfx(SimpleXMLElement $node)
    {
        throw new \Exception('loadOfx method not defined in class "' . get_class() . '"');
    }
}

