<?php

namespace OfxParser;

require_once "../lib/OfxParser/Parser.php";
require_once "../lib/OfxParser/TransactionEntity.php";

class ParserTest extends \PHPUnit_Framework_TestCase
{
	public function testXmlLoadStringThrowsExceptionWithInvalidXml()
	{
		$invalidXml = '<invalid xml>';

		$method = new \ReflectionMethod('\OfxParser\Parser', 'xmlLoadString');
		$method->setAccessible(true);

		try
		{
			$method->invoke(new Parser(), $invalidXml);
		}
		catch (\Exception $e)
		{
			if (stripos($e->getMessage(), 'Failed to parse OFX') !== false)
			{
				return true;
			}

			throw $e;
		}

		$this->fail('Method xmlLoadString did not raise an expected exception parsing an invalid XML string');
	}

	public function testXmlLoadStringLoadsValidXml()
	{
		$validXml = '<fooRoot><foo>bar</foo></fooRoot>';

		$method = new \ReflectionMethod('\OfxParser\Parser', 'xmlLoadString');
		$method->setAccessible(true);

		$xml = $method->invoke(new Parser(), $validXml);

		$this->assertInstanceOf('SimpleXMLElement', $xml);
		$this->assertEquals('bar', (string)$xml->foo);
	}

	public function testCloseUnclosedXmlTags()
	{
		$method = new \ReflectionMethod('\OfxParser\Parser', 'closeUnclosedXmlTags');
		$method->setAccessible(true);

		$parser = new Parser();

		$this->assertEquals('<SOMETHING>', $method->invoke($parser, '<SOMETHING>'));
		$this->assertEquals('<SOMETHING>foo</SOMETHING>', $method->invoke($parser, '<SOMETHING>foo'));
		$this->assertEquals('<SOMETHING>foo</SOMETHING>', $method->invoke($parser, '<SOMETHING>foo</SOMETHING>'));
	}

	public function testConvertSgmlToXml()
	{
		$sgml = <<<HERE
<SOMETHING>
	<FOO>bar
	<BAZ>bat</BAZ>
</SOMETHING>
HERE;
		$sgml = str_replace("\n", "\r\n", $sgml);

		$expected = <<<HERE
<SOMETHING>
<FOO>bar</FOO>
<BAZ>bat</BAZ>
</SOMETHING>
HERE;

		$method = new \ReflectionMethod('\OfxParser\Parser', 'convertSgmlToXml');
		$method->setAccessible(true);

		$this->assertEquals($expected, $method->invoke(new Parser, $sgml));
	}

	public function testGetTransactions()
	{
		$prop = new \ReflectionProperty('\OfxParser\Parser', 'transactions');
		$prop->setAccessible(true);

		$parser = new Parser();
		$prop->setValue($parser, 'test');

		$this->assertEquals('test', $parser->getTransactions());
	}

	public function testLoadFromFileWhenFileDoesNotExist()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$parser = new Parser();
		$parser->loadFromFile('a non-existent file');
	}

	public function testLoadFromFileWhenFileDoesExist()
	{
		if (!file_exists('ofxdata.ofx'))
		{
			$this->markTestSkipped('Could not find data file, cannot test loadFromFile method fully');
		}

		$parser = $this->getMock('\OfxParser\Parser', array('loadFromString'));
		$parser->expects($this->once())->method('loadFromString');
		$parser->loadFromFile('ofxdata.ofx');
	}

	public function testLoadFromString()
	{
		if (!file_exists('ofxdata.ofx'))
		{
			$this->markTestSkipped('Could not find data file, cannot test loadFromString method fully');
		}

		$content = file_get_contents('ofxdata.ofx');

		$parser = new Parser();
		$parser->loadFromString($content);
	}

	public function testCreateDateTimeFromOFXDateFormats()
	{
		// October 5, 2008, at 1:22 and 124 milliseconds pm, Easter Standard Time
		$expectedDateTime = new \DateTime('2008-10-05 13:22:00');

		$method = new \ReflectionMethod('\OfxParser\Parser', 'createDateTimeFromStr');
		$method->setAccessible(true);

		$parser = new Parser();

		// Test OFX Date Format YYYYMMDDHHMMSS.XXX[gmt offset:tz name]
		$DateTimeOne = $method->invoke($parser, '20081005132200.124[-5:EST]');
		$this->assertEquals($expectedDateTime->getTimestamp(), $DateTimeOne->getTimestamp());

		// Test YYYYMMDD
		$DateTimeTwo = $method->invoke($parser, '20081005');
        $this->assertEquals($expectedDateTime->format('Y-m-d'), $DateTimeTwo->format('Y-m-d'));

        // Test YYYYMMDDHHMMSS
        $DateTimeThree = $method->invoke($parser, '20081005132200');
        $this->assertEquals($expectedDateTime->getTimestamp(), $DateTimeThree->getTimestamp());

        // Test YYYYMMDDHHMMSS.XXX
        $DateTimeFour = $method->invoke($parser, '20081005132200.124');
		$this->assertEquals($expectedDateTime->getTimestamp(), $DateTimeFour->getTimestamp());
	}
}
