<?php

namespace OfxParserTest;

use PHPUnit\Framework\TestCase;
use OfxParser\Ofx;

/**
 * @covers OfxParser\Ofx
 */
class OfxTest extends TestCase
{
    /**
     * @var \SimpleXMLElement
     */
    protected $ofxData;

    public function setUp()
    {
        $ofxFile = dirname(__DIR__).'/fixtures/ofxdata-xml.ofx';

        if (!file_exists($ofxFile)) {
            self::markTestSkipped('Could not find data file, cannot test Ofx Class');
        }
        $this->ofxData = simplexml_load_string(file_get_contents($ofxFile));
    }

    public function testBuildsSignOn()
    {
        $ofx = new Ofx($this->ofxData);
        self::assertEquals('', $ofx->signOn->status->message);
        self::assertEquals('0', $ofx->signOn->status->code);
        self::assertEquals('INFO', $ofx->signOn->status->severity);
        self::assertEquals('Success', $ofx->signOn->status->codeDesc);

        self::assertInstanceOf('DateTime', $ofx->signOn->date);
        self::assertEquals('ENG', $ofx->signOn->language);
        self::assertEquals('MYBANK', $ofx->signOn->institute->name);
        self::assertEquals('01234', $ofx->signOn->institute->id);
    }

    public function testBuildsMultipleBankAccounts()
    {
        $multiOfxFile = dirname(__DIR__).'/fixtures/ofx-multiple-accounts-xml.ofx';
        if (!file_exists($multiOfxFile)) {
            self::markTestSkipped('Could not find multiple account data file, cannot fully test Multiple Bank Accounts');
        }
        $multiOfxData = simplexml_load_string(file_get_contents($multiOfxFile));
        $ofx = new Ofx($multiOfxData);

        self::assertCount(3, $ofx->bankAccounts);
        self::assertEmpty($ofx->bankAccount);
    }

    public function testBuildsBankAccount()
    {
        $Ofx = new Ofx($this->ofxData);

        $bankAccount = $Ofx->bankAccount;
        self::assertEquals('23382938', $bankAccount->transactionUid);
        self::assertEquals('098-121', $bankAccount->accountNumber);
        self::assertEquals('987654321', $bankAccount->routingNumber);
        self::assertEquals('SAVINGS', $bankAccount->accountType);
        self::assertEquals('5250.00', $bankAccount->balance);
        self::assertInstanceOf('DateTime', $bankAccount->balanceDate);

        $statement = $bankAccount->statement;
        self::assertEquals('USD', $statement->currency);
        self::assertInstanceOf('DateTime', $statement->startDate);
        self::assertInstanceOf('DateTime', $statement->endDate);

        $transactions = $statement->transactions;
        self::assertCount(3, $transactions);

        $expectedTransactions = [
           [
              'type' => 'CREDIT',
              'typeDesc' => 'Generic credit',
              'amount' => '200.00',
              'uniqueId' => '980315001',
              'name' => 'DEPOSIT',
              'memo' => 'automatic deposit',
              'sic' => '',
              'checkNumber' => ''
           ],
           [
               'type' => 'CREDIT',
               'typeDesc' => 'Generic credit',
               'amount' => '150.00',
               'uniqueId' => '980310001',
               'name' => 'TRANSFER',
               'memo' => 'Transfer from checking',
               'sic' => '',
               'checkNumber' => ''
           ],
           [
               'type' => 'CHECK',
               'typeDesc' => 'Cheque',
               'amount' => '-100.00',
               'uniqueId' => '980309001',
               'name' => 'Cheque',
               'memo' => '',
               'sic' => '',
               'checkNumber' => '1025'
           ],

        ];

        foreach ($transactions as $i => $transaction) {
            self::assertEquals($expectedTransactions[$i]['type'], $transaction->type);
            self::assertEquals($expectedTransactions[$i]['typeDesc'], $transaction->typeDesc);
            self::assertEquals($expectedTransactions[$i]['amount'], $transaction->amount);
            self::assertEquals($expectedTransactions[$i]['uniqueId'], $transaction->uniqueId);
            self::assertEquals($expectedTransactions[$i]['name'], $transaction->name);
            self::assertEquals($expectedTransactions[$i]['memo'], $transaction->memo);
            self::assertEquals($expectedTransactions[$i]['sic'], $transaction->sic);
            self::assertEquals($expectedTransactions[$i]['checkNumber'], $transaction->checkNumber);
            self::assertInstanceOf('DateTime', $transaction->date);
            self::assertInstanceOf('DateTime', $transaction->userInitiatedDate);
        }
    }
}
