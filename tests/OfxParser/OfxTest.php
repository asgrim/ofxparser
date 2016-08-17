<?php

namespace OfxParser;

require_once __DIR__ . '/../../vendor/autoload.php';

class OfxTest extends \PHPUnit_Framework_TestCase
{
    protected $ofxdata;

    public function setUp()
    {
        $ofxFile = dirname(__DIR__).'/fixtures/ofxdata-xml.ofx';

        if (!file_exists($ofxFile))
        {
            $this->markTestSkipped('Could not find data file, cannot test Ofx Class');
        }
        $this->ofxdata = simplexml_load_string( file_get_contents($ofxFile) );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage Argument 1 passed to OfxParser\Ofx::__construct() must be an instance of SimpleXMLElement, string given
     */
    public function testAcceptOnlySimpleXMLElement()
    {
        new Ofx('This is not an SimpleXMLObject');
    }

    public function testCreateDateTimeFromOFXDateFormats()
    {
        // October 5, 2008, at 1:22 and 124 milliseconds pm, Easter Standard Time
        $expectedDateTime = new \DateTime('2008-10-05 13:22:00');

        $method = new \ReflectionMethod('\OfxParser\Ofx', 'createDateTimeFromStr');
        $method->setAccessible(true);

        $Ofx = new Ofx($this->ofxdata);

        // Test OFX Date Format YYYYMMDDHHMMSS.XXX[gmt offset:tz name]
        $DateTimeOne = $method->invoke($Ofx, '20081005132200.124[-5:EST]');
        $this->assertEquals($expectedDateTime->getTimestamp(), $DateTimeOne->getTimestamp());

        // Test YYYYMMDD
        $DateTimeTwo = $method->invoke($Ofx, '20081005');
        $this->assertEquals($expectedDateTime->format('Y-m-d'), $DateTimeTwo->format('Y-m-d'));

        // Test YYYYMMDDHHMMSS
        $DateTimeThree = $method->invoke($Ofx, '20081005132200');
        $this->assertEquals($expectedDateTime->getTimestamp(), $DateTimeThree->getTimestamp());

        // Test YYYYMMDDHHMMSS.XXX
        $DateTimeFour = $method->invoke($Ofx, '20081005132200.124');
        $this->assertEquals($expectedDateTime->getTimestamp(), $DateTimeFour->getTimestamp());
    }

    public function testBuildsSignOn()
    {
        $Ofx = new Ofx($this->ofxdata);
        $this->assertEquals('', $Ofx->SignOn->Status->message);
        $this->assertEquals('0', $Ofx->SignOn->Status->code);
        $this->assertEquals('INFO', $Ofx->SignOn->Status->severity);
        $this->assertEquals('Success', $Ofx->SignOn->Status->codeDesc);

        $this->assertInstanceOf('DateTime', $Ofx->SignOn->date);
        $this->assertEquals('ENG', $Ofx->SignOn->language);
        $this->assertEquals('MYBANK', $Ofx->SignOn->Institute->name);
        $this->assertEquals('01234', $Ofx->SignOn->Institute->id);
    }

    public function testBuildsMultipleBankAccounts()
    {
        $multiOfxFile = dirname(__DIR__).'/fixtures/ofx-multiple-accounts-xml.ofx';
        if (!file_exists($multiOfxFile))
        {
            $this->markTestSkipped('Could not find multiple account data file, cannot fully test Multiple Bank Accounts');
        }
        $multiOfxData = simplexml_load_string( file_get_contents($multiOfxFile) );
        $Ofx = new Ofx($multiOfxData);

        $this->assertCount(3, $Ofx->BankAccounts);
        $this->assertEmpty($Ofx->BankAccount);
    }

     public function testBuildsBankAccount()
     {
         $Ofx = new Ofx($this->ofxdata);

         $Account = $Ofx->BankAccount;
         $this->assertEquals('23382938', $Account->transactionUid);
         $this->assertEquals('098-121', $Account->accountNumber);
         $this->assertEquals('987654321', $Account->routingNumber);
         $this->assertEquals('SAVINGS', $Account->accountType);
         $this->assertEquals('5250.00', $Account->balance);
         $this->assertInstanceOf('DateTime', $Account->balanceDate);

         $Statement = $Account->Statement;
         $this->assertEquals('USD', $Statement->currency);
         $this->assertInstanceOf('DateTime', $Statement->startDate);
         $this->assertInstanceOf('DateTime', $Statement->endDate);

         $Transactions = $Statement->transactions;
         $this->assertCount(3, $Transactions);

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

         foreach( $Transactions as $i => $transaction )
         {
             $this->assertEquals($expectedTransactions[$i]['type'], $transaction->type);
             $this->assertEquals($expectedTransactions[$i]['typeDesc'], $transaction->typeDesc);
             $this->assertEquals($expectedTransactions[$i]['amount'], $transaction->amount);
             $this->assertEquals($expectedTransactions[$i]['uniqueId'], $transaction->uniqueId);
             $this->assertEquals($expectedTransactions[$i]['name'], $transaction->name);
             $this->assertEquals($expectedTransactions[$i]['memo'], $transaction->memo);
             $this->assertEquals($expectedTransactions[$i]['sic'], $transaction->sic);
             $this->assertEquals($expectedTransactions[$i]['checkNumber'], $transaction->checkNumber);
             $this->assertInstanceOf('DateTime', $transaction->date);
         }
     }
}
