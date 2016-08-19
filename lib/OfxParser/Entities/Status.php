<?php

namespace OfxParser\Entities;

class Status extends AbstractEntity
{
    /**
     * @var string[]
     */
    private static $codes = [
        '0'       => 'Success',
        '2000'    => 'General error',
        '15000'   => 'Must change USERPASS',
        '15500'   => 'Signon invalid',
        '15501'   => 'Customer account already in use',
        '15502'   => 'USERPASS Lockout'
    ];

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $severity;

    /**
     * @var string
     */
    public $message;

    /**
     * Get the associated code description
     *
     * @return string
     */
    public function codeDesc()
    {
        // Cast code to string from SimpleXMLObject
        $code = (string)$this->code;
        return array_key_exists($code, self::$codes) ? self::$codes[$code] : '';
    }
}
