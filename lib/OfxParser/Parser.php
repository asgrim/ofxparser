<?php

namespace OfxParser;

/**
 * An OFX parser library
 *
 * Heavily refactored from Guillaume Bailleul's grimfor/ofxparser
 *
 * @author Guillaume BAILLEUL <contact@guillaume-bailleul.fr>
 * @author James Titcumb <hello@jamestitcumb.com>
 */
class Parser
{
	/**
	 * @var array of TransactionEntity objects
	 */
	private $transactions;

	/**
	 * Load an OFX file into this parser by way of a filename
	 *
	 * @param string $ofxFile A path that can be loaded with file_get_contents
	 * @throws \InvalidArgumentException
	 */
	public function loadFromFile($ofxFile)
	{
		if (file_exists($ofxFile))
		{
			$this->loadFromString(file_get_contents($ofxFile));
		}
		else
		{
			throw new \InvalidArgumentException("File '{$ofxFile}' could not be found");
		}
	}

	/**
	 * Load an OFX by directly using the text content
	 *
	 * @param string $ofxContent
	 * @throws \Exception
	 */
	public function loadFromString($ofxContent)
	{
		$sgmlStart = stripos($ofxContent, '<OFX>');
		$ofxHeader = trim(substr($ofxContent, 0, $sgmlStart));
		$ofxSgml = trim(substr($ofxContent, $sgmlStart));

		$ofxXml = $this->convertSgmlToXml($ofxSgml);

		$xml = $this->xmlLoadString($ofxXml);

		foreach ($xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN as $xmlTx)
		{
			$transaction = new TransactionEntity();
			$transaction->Amount = (float)$xmlTx->TRNAMT;
			$transaction->TransactionType = (string)$xmlTx->TRNTYPE;
			$transaction->Date = new \DateTime($xmlTx->DTPOSTED);
			$transaction->UniqueId = (string)$xmlTx->FITID;
			$transaction->Name = (string)$xmlTx->NAME;
			$transaction->Memo = (string)$xmlTx->MEMO;

			$this->transactions[] = $transaction;
		}
	}

	/**
	 * Load an XML string without PHP errors - throws exception instead
	 *
	 * @param string $xmlString
	 * @throws \Exception
	 * @return \SimpleXMLElement
	 */
	private function xmlLoadString($xmlString)
	{
		libxml_clear_errors();
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($xmlString);

		if ($errors = libxml_get_errors())
		{
			throw new \Exception("Failed to parse OFX: " . var_export($errors, true));
		}

		return $xml;
	}

	/**
	 * Detect any unclosed XML tags - if they exist, close them
	 *
	 * @param string $line
	 * @return $line
	 */
	private function closeUnclosedXmlTags($line)
	{
		// Matches: <SOMETHING>blah
		// Does not match: <SOMETHING>
		// Does not match: <SOMETHING>blah</SOMETHING>
		if (preg_match("/<([A-Za-z0-9.]+)>([\w0-9\.\-\_\+\, :\[\]]+)$/", trim($line), $matches))
		{
			return "<{$matches[1]}>{$matches[2]}</{$matches[1]}>";
		}
		return $line;
	}

	/**
	 * Convert an SGML to an XML string
	 *
	 * @param string $sgml
	 * @return string
	 */
	private function convertSgmlToXml($sgml)
	{
		$sgml = str_replace("\r\n", "\n", $sgml);
		$sgml = str_replace("\r", "\n", $sgml);

		$lines = explode("\n", $sgml);

		$xml = "";
		foreach ($lines as $line)
		{
			$xml .= trim($this->closeUnclosedXmlTags($line)) . "\n";
		}

		return trim($xml);
	}

	/**
	 * Get the transactions that have been processed
	 *
	 * @return array
	 */
	public function getTransactions()
	{
		return $this->transactions;
	}
}
