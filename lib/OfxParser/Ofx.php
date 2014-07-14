<?php

namespace OfxParser;

use OfxParser\Entities\BankAccount,
    OfxParser\Entities\Transaction,
    OfxParser\Entities\Statement,
    OfxParser\Entities\Status,
    OfxParser\Entities\SignOn;



/**
 * The OFX object
 *
 * Heavily refactored from Guillaume Bailleul's grimfor/ofxparser
 *
 * Second refactor by Oliver Lowe to unify the API across all
 * OFX data-types.
 *
 * Based on Andrew A Smith's Ruby ofx-parser
 *
 * @author Guillaume BAILLEUL <contact@guillaume-bailleul.fr>
 * @author James Titcumb <hello@jamestitcumb.com>
 * @author Oliver Lowe <mrtriangle@gmail.com>
 */
class Ofx
{
    public $Header;
    public $SignOn;
    public $SignupAccountInfo;
    public $BankAccount;
    public $CreditCard;
    public $Investment;

    public function __construct($xml)
    {
        $this->SignOn = $this->buildSignOn($xml->SIGNONMSGSRSV1->SONRS);
        $this->SignupAccountInfo = $this->buildAccountInfo($xml->SIGNUPMSGSRSV1->ACCTINFOTRNRS);
        $this->BankAccount = $this->buildBankAccount($xml->BANKMSGSRSV1->STMTTRNRS);
        $this->CreditCard = $this->buildCreditCard($xml->CREDITCARDMSGSRSV1->CCSTMTTRNRS);
    }

    /**
     * Get the transactions that have been processed
     *
     * @return array
     */
    public function getTransactions()
    {
        return $this->BankAccount->Statement->Transactions;
    }

    private function buildSignOn($xml)
    {
        $SignOn = new SignOn();
        $SignOn->status = build_status((doc/"STATUS"));
        $SignOn->date = $this->createDateTimeFromStr($xml->DTSERVER);
        $SignOn->language = $xml->LANGUAGE;

        $SignOn->Institute = new Institute();
        $SignOn->Institute->name = $xml->FI->ORG;
        $SignOn->Institute->id = $xml->FI->FID;
        return $SignOn;
    }

    private function buildAccountInfo($xml)
    {
        foreach( $xml->ACCTINFO as $account ) {
            $AccountInfo = new AccountInfo();
            $AccountInfo->desc = $account->DESC;
            $AccountInfo->number = $account->ACCTID;
        }
    }

    private function buildBankAccount($xml)
    {
        $Bank = new BankAccount();
        $Bank->transactionUid = $xml->TRNUID;
        $Bank->accountNumber = $xml->STMTRS->BANKACCTFROM->ACCTID;
        $Bank->routingNumber = $xml->STMTRS->BANKACCTFROM->BANKID;
        $Bank->type = $xml->STMTRS->BANKACCTFROM->ACCTTYPE;
        $Bank->balance = $xml->STMTRS->LEDGERBAL->BALAMT;
        $Bank->balanceDate = $this->createDateTimeFromStr($xml->STMTRS->LEDGERBAL->DTASOF);

        $Bank->Statement = new Statement();
        $Bank->Statement->currency = $xml->STMTRS->CURDEF;
        $Bank->Statement->startDate = $this->createDateTimeFromStr($xml->STMTRS->BANKTRANLIST->DTSTART);
        $Bank->Statement->endDate = $this->createDateTimeFromStr($xml->STMTRS->BANKTRANLIST->DTEND);
        $Bank->Statement->transactions = $this->buildTransactions($xml->STMTRS->BANKTRANLIST->STMTTRN);

        return $Bank;
    }

    private function buildTransactions($transactions)
    {
        $return = array();
        foreach( $transactions as $t ) {
            $Transaction = new Transaction();
            $Transaction->type = (string) $t->TRNTYPE;
            $Transaction->date = $this->createDateTimeFromStr($t->DTPOSTED);
            $Transaction->amount = (float) $t->TRNAMT;
            $Transaction->fitId = (int) $t->FITID;
            $Transaction->payee = (string) $t->NAME;
            $Transaction->memo = (string) $t->MEMO;
            $Transaction->sic = $t->SIC;
            $Transaction->checkNumber = $Transaction->Type=="CHECK" ? $t->CHECKNUM : "";
            $return[] = $Transaction;
        }
        return $return;
    }

    private function buildStatus($xml)
    {
      $Status = new Status();
      $Status->code = $xml->CODE;
      $Status->severity = $xml->SEVERITY;
      $Status->message = $xml->MESSAGE;
      return $Status;
    }


    /**
     * Create a DateTime object from a valid OFX date format
     *
     * Supports:
     * YYYYMMDDHHMMSS.XXX[gmt offset:tz name]
     * YYYYMMDDHHMMSS.XXX
     * YYYYMMDDHHMMSS
     * YYYYMMDD
     *
     * @param  string $dateString
     * @return \DateTime | $dateString
     */
    private function createDateTimeFromStr($dateString)
    {
        $regex = "/"
                ."(\d{4})(\d{2})(\d{2})?"       // YYYYMMDD             1,2,3
                ."(?:(\d{2})(\d{2})(\d{2}))?"   // HHMMSS   - optional  4,5,6
                ."(?:\.(\d{3}))?"               // .XXX     - optional  7
                ."(?:\[(-?\d+)\:(\w{3}\]))?"    // [-n:TZ]  - optional  8,9
                ."/";

        if (preg_match($regex, $dateString, $matches))
        {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            $hour = isset($matches[4]) ? $matches[4] : 0;
            $min = isset($matches[5]) ? $matches[5] : 0;
            $sec = isset($matches[6]) ? $matches[6] : 0;

            $format = $year.'-'.$month.'-'.$day.' '.$hour.':'.$min.':'.$sec;
            return new \DateTime($format);
        }
        return $dateString;
    }
}