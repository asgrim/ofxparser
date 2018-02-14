<?php

namespace OfxParser;

/**
 * An OFX parser library
 *
 * Heavily refactored from Guillaume Bailleul's grimfor/ofxparser
 *
 * @author Guillaume BAILLEUL <contact@guillaume-bailleul.fr>
 * @author James Titcumb <hello@jamestitcumb.com>
 * @author Oliver Lowe <mrtriangle@gmail.com>
 */
class Parser
{
    /**
     * Load an OFX file into this parser by way of a filename
     *
     * @param string $ofxFile A path that can be loaded with file_get_contents
     * @return Ofx
     * @throws \Exception
     */
    public function loadFromFile($ofxFile)
    {
        if (!file_exists($ofxFile)) {
            throw new \InvalidArgumentException("File '{$ofxFile}' could not be found");
        }

        return $this->loadFromString(file_get_contents($ofxFile));
    }

    /**
     * Load an OFX by directly using the text content
     *
     * @param string $ofxContent
     * @return  Ofx
     * @throws \Exception
     */
    public function loadFromString($ofxContent)
    {
        $ofxContent = utf8_encode($ofxContent);
        $sgmlStart = stripos($ofxContent, '<OFX>');
        $ofxSgml = trim($this->normalizeNewlines(substr($ofxContent, $sgmlStart)));

        $ofxXml = $this->convertSgmlToXml($ofxSgml);

        $xml = $this->xmlLoadString($ofxXml);

        return new Ofx($xml);
    }

    /**
     * Normalize newlines by removing and adding newlines only before opening tags
     *
     * @param string $ofxContent
     * @return string
     */
    private function normalizeNewlines($ofxContent)
    {
        // clear all new line characters first
        $ofxContent = str_replace(["\r", "\n"], '', $ofxContent);
        // add line breaks before opening tags only, to allow XML to parse
        return preg_replace('/<[^\/!]/', "\n" . '$0', $ofxContent);
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

        if ($errors = libxml_get_errors()) {
            throw new \RuntimeException('Failed to parse OFX: ' . var_export($errors, true));
        }

        return $xml;
    }

    /**
     * Detect any unclosed XML tags - if they exist, close them
     *
     * @param string $line
     * @return string
     */
    private function closeUnclosedXmlTags($line)
    {
        $line = trim($line);
        $tag = ltrim(substr($line, 1, strpos($line, '>') - 1), '/');

        // Line is "<SOMETHING>" or "</SOMETHING>"
        if ($line === '<' . $tag . '>' || $line === '</' . $tag . '>') {
            return $line;
        }

        // Tag is properly closed
        if (strpos($line, '</' . $tag . '>') !== false) {
            return $line;
        }

        $lines = explode("\n", str_replace('</', "\n" . '</', $line));
        $lines[0] = trim($lines[0]) . '</' . $tag .'>';
        return implode('', $lines);
    }

    /**
     * Convert an SGML to an XML string
     *
     * @param string $sgml
     * @return string
     */
    private function convertSgmlToXml($sgml)
    {
        $xml = '';
        foreach (explode("\n", $sgml) as $line) {
            $xml .= $this->closeUnclosedXmlTags($line) . "\n";
        }

        return rtrim($xml);
    }
}
