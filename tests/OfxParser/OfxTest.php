<?php

namespace OfxParser;

require_once "../vendor/autoload.php";

class OfxTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateDateTimeFromOFXDateFormats()
    {
        // October 5, 2008, at 1:22 and 124 milliseconds pm, Easter Standard Time
        $expectedDateTime = new \DateTime('2008-10-05 13:22:00');

        $method = new \ReflectionMethod('\OfxParser\Ofx', 'createDateTimeFromStr');
        $method->setAccessible(true);

        $Ofx = new Ofx('dummy-xml');

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

    public function testOfxBuildsBank()
    {
        $Ofx = new Ofx('dummy-xml');
    //     var_dump($Ofx);
    //     $Ofx->BankAccount->Statement->Transactions = 'test';
    }

    // public function testGetTransactions()
    // {
    //     $Ofx = new Ofx('dummy-xml');
    //     var_dump($Ofx);
    //     $Ofx->BankAccount->Statement->Transactions = 'test';

    //     $this->assertEquals('test', $Ofx->getTransactions());
    // }

}
