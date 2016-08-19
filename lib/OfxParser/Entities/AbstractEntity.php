<?php

namespace OfxParser\Entities;

abstract class AbstractEntity
{
    /**
     * Allow functions to be called as properties
     * to unify the API
     *
     * @param $name
     * @return mixed|bool
     */
    public function __get($name)
    {
        if (method_exists($this, lcfirst($name))) {
            return $this->{$name}();
        }
        return false;
    }
}
