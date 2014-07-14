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
    public $Code;
    public $Severity;
    public $Message;

    public function codeDesc()
    {
        return isset($this->codes[$this->Code]) ? $this->codes[$this->code] : '';
    }

}