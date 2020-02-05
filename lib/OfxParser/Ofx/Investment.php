<?php

namespace OfxParser\Ofx;

use SimpleXMLElement;
use OfxParser\Ofx;
use OfxParser\Utils;
use OfxParser\Entities\Statement;
use OfxParser\Entities\Investment\Account as InvestmentAccount;
use OfxParser\Entities\Investment\Transaction\Banking;
use OfxParser\Entities\Investment\Transaction\BuyMutualFund;
use OfxParser\Entities\Investment\Transaction\BuySecurity;
use OfxParser\Entities\Investment\Transaction\BuyStock;
use OfxParser\Entities\Investment\Transaction\Income;
use OfxParser\Entities\Investment\Transaction\Reinvest;
use OfxParser\Entities\Investment\Transaction\SellMutualFund;

class Investment extends Ofx
{
    /**
     * @param SimpleXMLElement $xml
     * @throws \Exception
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->signOn = $this->buildSignOn($xml->SIGNONMSGSRSV1->SONRS);

        if (isset($xml->INVSTMTMSGSRSV1)) {
            $this->bankAccounts = $this->buildAccounts($xml);
        }

        // Set a helper if only one bank account
        if (count($this->bankAccounts) === 1) {
            $this->bankAccount = $this->bankAccounts[0];
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array Array of InvestmentAccount enities
     * @throws \Exception
     */
    protected function buildAccounts(SimpleXMLElement $xml)
    {
        // Loop through the bank accounts
        $accounts = [];
        foreach ($xml->INVSTMTMSGSRSV1->INVSTMTTRNRS as $accountStatement) {
            foreach ($accountStatement->INVSTMTRS as $statementResponse) {
                $accounts[] = $this->buildAccount($accountStatement->TRNUID, $statementResponse);
            }
        }
        return $accounts;
    }

    /**
     * @param string $transactionUid
     * @param SimpleXMLElement $statementResponse
     * @return InvestmentAccount
     * @throws \Exception
     */
    protected function buildAccount($transactionUid, SimpleXMLElement $statementResponse)
    {
        $account = new InvestmentAccount();
        $account->transactionUid = (string) $transactionUid;
        $account->brokerId = (string) $statementResponse->INVACCTFROM->BROKERID;
        $account->accountNumber = (string) $statementResponse->INVACCTFROM->ACCTID;

        $account->statement = new Statement();
        $account->statement->currency = (string) $statementResponse->CURDEF;

        $account->statement->startDate = Utils::createDateTimeFromStr(
            $statementResponse->INVTRANLIST->DTSTART
        );

        $account->statement->endDate = Utils::createDateTimeFromStr(
            $statementResponse->INVTRANLIST->DTEND
        );

        $account->statement->transactions = $this->buildTransactions(
            $statementResponse->INVTRANLIST->children()
        );

        return $account;
    }

    /**
     * Processes multiple types of investment transactions, ignoring many
     * others.
     *
     * @param SimpleXMLElement $transactions
     * @return array
     * @throws \Exception
     */
    protected function buildTransactions(SimpleXMLElement $transactions)
    {
        $activity = [];

        foreach ($transactions as $t) {
            $item = null;

            switch ($t->getName()) {
                case 'BUYMF':
                    $item = new BuyMutualFund();
                    break;
                case 'BUYOTHER':
                    $item = new BuySecurity();
                    break;
                case 'BUYSTOCK':
                    $item = new BuyStock();
                    break;
                case 'INCOME':
                    $item = new Income();
                    break;
                case 'INVBANKTRAN':
                    $item = new Banking();
                    break;
                case 'REINVEST':
                    $item = new Reinvest();
                    break;
                case 'SELLMF':
                    $item = new SellMutualFund();
                    break;
                case 'DTSTART':
                    // already processed
                    break;
                case 'DTEND':
                    // already processed
                    break;
                default:
                    // Log: ignored node....
                    break;
            }

            if (!is_null($item)) {
                $item->loadOfx($t);
                $activity[] = $item;
            }
        }

        return $activity;
    }
}
