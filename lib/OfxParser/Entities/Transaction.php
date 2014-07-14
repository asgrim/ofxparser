<?php

namespace OfxParser\Entities;

class Transaction extends AbstractEntity
{

    protected $types = array(
        "CREDIT"      => "Generic credit",
        "DEBIT"       => "Generic debit",
        "INT"         => "Interest earned or paid ",
        "DIV"         => "Dividend",
        "FEE"         => "FI fee",
        "SRVCHG"      => "Service charge",
        "DEP"         => "Deposit",
        "ATM"         => "ATM debit or credit",
        "POS"         => "Point of sale debit or credit ",
        "XFER"        => "Transfer",
        "CHECK"       => "Cheque",
        "PAYMENT"     => "Electronic payment",
        "CASH"        => "Cash withdrawal",
        "DIRECTDEP"   => "Direct deposit",
        "DIRECTDEBIT" => "Merchant initiated debit",
        "REPEATPMT"   => "Repeating payment/standing order",
        "OTHER"       => "Other"
    );

	public $type;
	public $date;
	public $amount;
	public $uniqueId;
	public $name;
	public $memo;

    public function TypeDescription()
    {
        return isset($this->types[$this->type]) ? $this->types[$this->type] : '';
    }
}