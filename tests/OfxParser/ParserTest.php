<?php

namespace OfxParser;

require_once __DIR__ . '/../../vendor/autoload.php';

class ParserTest extends \PHPUnit_Framework_TestCase
{
    protected $ofxdata;

    public function setUp()
    {
        $this->ofxdata = 'fixtures/ofxdata.ofx';
    }

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

	public function testLoadFromFileWhenFileDoesNotExist()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$parser = new Parser();
		$parser->loadFromFile('a non-existent file');
	}

	public function testLoadFromFileWhenFileDoesExist()
	{
		if (!file_exists($this->ofxdata))
		{
			$this->markTestSkipped('Could not find data file, cannot test loadFromFile method fully');
		}

		$parser = $this->getMock('\OfxParser\Parser', ['loadFromString']);
		$parser->expects($this->once())->method('loadFromString');
		$parser->loadFromFile($this->ofxdata);
	}

	public function testLoadFromString()
	{
		if (!file_exists($this->ofxdata))
		{
			$this->markTestSkipped('Could not find data file, cannot test loadFromString method fully');
		}

		$content = file_get_contents($this->ofxdata);

		$parser = new Parser();
		$parser->loadFromString($content);
	}

}
