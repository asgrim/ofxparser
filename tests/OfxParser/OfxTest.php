<?php

namespace OfxParserTest;

use OfxParser\Ofx;

/**
 * @covers OfxParser\Ofx
 */
class OfxTest extends \PHPUnit_Framework_TestCase
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

    public function amountConversionProvider()
    {
        return [
            '1000.00' => ['1000.00', 1000.0],
            '1000,00' => ['1000,00', 1000.0],
            '1,000.00' => ['1,000.00', 1000.0],
            '1.000,00' => ['1.000,00', 1000.0],
            '-1000.00' => ['-1000.00', -1000.0],
            '-1000,00' => ['-1000,00', -1000.0],
            '-1,000.00' => ['-1,000.00', -1000.0],
            '-1.000,00' => ['-1.000,00', -1000.0],
            '1' => ['1', 1.0],
            '10' => ['10', 10.0],
            '100' => ['100', 1.0], // @todo this is weird behaviour, should not really expect this
        ];
    }

    /**
     * @param string $input
     * @param float $output
     * @dataProvider amountConversionProvider
     */
    public function testCreateAmountFromStr($input, $output)
    {
        $method = new \ReflectionMethod(Ofx::class, 'createAmountFromStr');
        $method->setAccessible(true);

        $ofx = new Ofx($this->ofxData);

        self::assertSame($output, $method->invoke($ofx, $input));
    }

    public function testCreateDateTimeFromOFXDateFormats()
    {
        // October 5, 2008, at 1:22 and 124 milliseconds pm, Easter Standard Time
        $expectedDateTime = new \DateTime('2008-10-05 13:22:00');

        $method = new \ReflectionMethod(Ofx::class, 'createDateTimeFromStr');
        $method->setAccessible(true);

        $Ofx = new Ofx($this->ofxData);

        // Test OFX Date Format YYYYMMDDHHMMSS.XXX[gmt offset:tz name]
        $DateTimeOne = $method->invoke($Ofx, '20081005132200.124[-5:EST]');
        self::assertEquals($expectedDateTime->getTimestamp(), $DateTimeOne->getTimestamp());

        // Test YYYYMMDD
        $DateTimeTwo = $method->invoke($Ofx, '20081005');
        self::assertEquals($expectedDateTime->format('Y-m-d'), $DateTimeTwo->format('Y-m-d'));

        // Test YYYYMMDDHHMMSS
        $DateTimeThree = $method->invoke($Ofx, '20081005132200');
        self::assertEquals($expectedDateTime->getTimestamp(), $DateTimeThree->getTimestamp());

        // Test YYYYMMDDHHMMSS.XXX
        $DateTimeFour = $method->invoke($Ofx, '20081005132200.124');
        self::assertEquals($expectedDateTime->getTimestamp(), $DateTimeFour->getTimestamp());
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
