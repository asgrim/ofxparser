<?php

namespace OfxParser\Entities;

class Status extends AbstractEntity
{
    protected $codes= [
        '0'       => 'Success',
        '2000'    => 'General error',
        '15000'   => 'Must change USERPASS',
        '15500'   => 'Signon invalid',
        '15501'   => 'Customer account already in use',
        '15502'   => 'USERPASS Lockout'
    ];

    public $code;
    public $severity;
    public $message;

    /**
     * Get the associated code description
     *
     * @return string
     */
    public function codeDesc()
    {
        // Cast code to string from SimpleXMLObject
        $code = (string) $this->code;
        return isset($this->codes[$code]) ? $this->codes[$code] : '';
    }

}