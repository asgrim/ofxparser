<?php

namespace OfxParser\Entities;

class Status extends AbstractEntity
{
    protected $codes= array (
        '0'       => 'Success',
        '2000'    => 'General error',
        '15000'   => 'Must change USERPASS',
        '15500'   => 'Signon invalid',
        '15501'   => 'Customer account already in use',
        '15502'   => 'USERPASS Lockout'
    );
    public $code;
    public $severity;
    public $message;

    public function codeDesc()
    {
        return isset($this->codes[$this->code]) ? $this->codes[$this->code] : '';
    }

}