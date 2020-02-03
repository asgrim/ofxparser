<?php

namespace OfxParser\Parsers;

use PHPUnit\Framework\TestCase;
use OfxParser\Parsers\Investment as InvestmentParser;

/**
 * @covers OfxParser\Parsers\Investment
 */
class InvestmentTest extends TestCase
{
    public function testParseInvestmentsXML()
    {
        $parser = new InvestmentParser();
        $ofx = $parser->loadFromFile(__DIR__ . '/../../fixtures/ofxdata-investments-xml.ofx');

        $account = reset($ofx->bankAccounts);
        self::assertSame('TEST-UID-1', $account->transactionUid);
        self::assertSame('vanguard.com', $account->brokerId);
        self::assertSame('1234567890', $account->accountNumber);

        // Check some transactions:
        $expected = array(
            '100100' => array(
                'tradeDate' => new \DateTime('2010-01-01'),
                'settlementDate' => new \DateTime('2010-01-02'),
                'securityId' => '122022322',
                'securityIdType' => 'CUSIP',
                'units' => '31.25',
                'unitPrice' => '32.0',
                'total' => '-1000.0',
                'buyType' => 'BUY',
                'actionCode' => 'BUYMF',
            ),
            '100200' => array(
                'tradeDate' => new \DateTime('2011-02-01'),
                'settlementDate' => new \DateTime('2011-02-03'),
                'securityId' => '355055155',
                'securityIdType' => 'CUSIP',
                'units' => '3.0',
                'unitPrice' => '181.96',
                'total' => '-545.88',
                'buyType' => 'BUY',
                'actionCode' => 'BUYSTOCK',
            ),
            '100300' => array(
                'tradeDate' => new \DateTime('2010-01-01'),
                'settlementDate' => new \DateTime('2010-01-01'),
                'securityId' => '122022322',
                'securityIdType' => 'CUSIP',
                'units' => '-1000.0',
                'unitPrice' => '1.0',
                'total' => '1000.0',
                'sellType' => 'SELL',
                'actionCode' => 'SELLMF',
            ),
            '200100' => array(
                'tradeDate' => new \DateTime('2011-02-01'),
                'settlementDate' => new \DateTime('2011-02-01'),
                'securityId' => '822722622',
                'securityIdType' => 'CUSIP',
                'units' => '',
                'unitPrice' => '',
                'total' => '12.59',
                'incomeType' => 'DIV',
                'subAccountSec' => 'CASH',
                'subAccountFund' => 'CASH',
                'actionCode' => 'INCOME',
            ),
            '200200' => array(
                'tradeDate' => new \DateTime('2011-02-01'),
                'settlementDate' => new \DateTime('2011-02-01'),
                'securityId' => '355055155',
                'securityIdType' => 'CUSIP',
                'units' => '0.037',
                'unitPrice' => '187.9894',
                'total' => '-6.97',
                'incomeType' => 'DIV',
                'subAccountSec' => 'CASH',
                'subAccountFund' => '',
                'actionCode' => 'REINVEST',
            ),
            '300100' => array(
                'date' => new \DateTime('2010-01-15'),
                'type' => 'OTHER',
                'amount' => 1234.56,
                'actionCode' => 'INVBANKTRAN',
            ),
        );

        if (count($expected)) {
            self::assertTrue(count($account->statement->transactions) > 0);
        }

        $this->validateTransactions($account->statement->transactions, $expected);
    }

    public function testParseInvestmentsXMLOneLine()
    {
        $parser = new InvestmentParser();
        $ofx = $parser->loadFromFile(__DIR__ . '/../../fixtures/ofxdata-investments-oneline-xml.ofx');

        $account = reset($ofx->bankAccounts);
        self::assertSame('TEST-UID-1', $account->transactionUid);
        self::assertSame('vanguard.com', $account->brokerId);
        self::assertSame('1234567890', $account->accountNumber);

        // Check some transactions:
        $expected = array(
            '100200' => array(
                'tradeDate' => new \DateTime('2011-02-01'),
                'settlementDate' => new \DateTime('2011-02-03'),
                'securityId' => '355055155',
                'securityIdType' => 'CUSIP',
                'units' => '3.0',
                'unitPrice' => '181.96',
                'total' => '-545.88',
                'buyType' => 'BUY',
                'actionCode' => 'BUYSTOCK',
            ),
        );

        if (count($expected)) {
            self::assertTrue(count($account->statement->transactions) > 0);
        }

        $this->validateTransactions($account->statement->transactions, $expected);
    }

    public function testParseInvestmentsXMLMultipleAccounts()
    {
        $parser = new InvestmentParser();
        $ofx = $parser->loadFromFile(__DIR__ . '/../../fixtures/ofxdata-investments-multiple-accounts-xml.ofx');

        // Check some transactions:
        $expected = array(
            '1234567890' => array(
                '100200' => array(
                    'tradeDate' => new \DateTime('2011-02-01'),
                    'settlementDate' => new \DateTime('2011-02-03'),
                    'securityId' => '355055155',
                    'securityIdType' => 'CUSIP',
                    'units' => '3.0',
                    'unitPrice' => '181.96',
                    'total' => '-545.88',
                    'buyType' => 'BUY',
                    'actionCode' => 'BUYSTOCK',
                ),
            ),
            '987654321' => array(
                '200200' => array(
                    'tradeDate' => new \DateTime('2011-02-01'),
                    'settlementDate' => new \DateTime('2011-02-01'),
                    'securityId' => '355055155',
                    'securityIdType' => 'CUSIP',
                    'units' => '0.037',
                    'unitPrice' => '187.9894',
                    'total' => '-6.97',
                    'incomeType' => 'DIV',
                    'subAccountSec' => 'CASH',
                    'subAccountFund' => '',
                    'actionCode' => 'REINVEST',
                ),
            ),
        );

        if (count($expected)) {
            self::assertEquals(count($ofx->bankAccounts), count($expected), 'Account count mismatch');
        }

        foreach ($ofx->bankAccounts as $account) {
            $myExpected = isset($expected[$account->accountNumber])
                ? $expected[$account->accountNumber]
                : array();

            $this->validateTransactions($account->statement->transactions, $myExpected);
        }
    }

    public function testGoogleFinanceInvestments()
    {
        $parser = new InvestmentParser();
        $ofx = $parser->loadFromFile(__DIR__ . '/../../fixtures/ofxdata-google.ofx');

        $account = reset($ofx->bankAccounts);
        self::assertSame('1001', $account->transactionUid);
        self::assertSame('google.com', $account->brokerId);
        self::assertSame('StockersTest', $account->accountNumber);

        // Check some transactions:
        $expected = array(
            '1' => array(
                'tradeDate' => new \DateTime('2010-04-01'),
                'securityId' => 'TSE:T',
                'securityIdType' => 'TICKER',
                'units' => '5',
                'unitPrice' => '20',
                'total' => '-100',
                'buyType' => 'BUY',
                'actionCode' => 'BUYSTOCK',
            ),
        );

        if (count($expected)) {
            self::assertTrue(count($account->statement->transactions) > 0);
        }

        $this->validateTransactions($account->statement->transactions, $expected);
    }

    protected function validateTransactions($transactions, $expected)
    {
        foreach ($transactions as $t) {
            if (isset($expected[$t->uniqueId])) {
                $data = $expected[$t->uniqueId];
                foreach ($data as $prop => $val) {
                    // TEMP:
                    if ($prop == 'actionCode') {
                        continue;
                    }

                    if ($val instanceof \DateTimeInterface) {
                        self::assertSame(
                            $val->format('Y-m-d'),
                            $t->{$prop}->format('Y-m-d'),
                            'Failed comparison for "' . $prop .'"'
                        );
                    } else {
                        self::assertSame(
                            $val,
                            $t->{$prop},
                            'Failed comparison for "' . $prop .'"'
                        );
                    }
                }
            }
        }
    }
}
