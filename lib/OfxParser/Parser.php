<?php

namespace OfxParser;

use SimpleXMLElement;

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
     * Factory to extend support for OFX document structures.
     * @param SimpleXMLElement $xml
     * @return Ofx
     */
    protected function createOfx(SimpleXMLElement $xml)
    {
        return new Ofx($xml);
    }

    /**
     * Load an OFX file into this parser by way of a filename
     *
     * @param string $ofxFile A path that can be loaded with file_get_contents
     * @return Ofx
     * @throws \InvalidArgumentException
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
     */
    public function loadFromString($ofxContent)
    {
        $ofxContent = str_replace(["\r\n", "\r"], "\n", $ofxContent);
        $ofxContent = utf8_encode($ofxContent);

        $sgmlStart = stripos($ofxContent, '<OFX>');
        $ofxHeader =  trim(substr($ofxContent, 0, $sgmlStart));
        $header = $this->parseHeader($ofxHeader);

        $ofxSgml = trim(substr($ofxContent, $sgmlStart));
        if (stripos($ofxHeader, '<?xml') === 0) {
            $ofxXml = $ofxSgml;
        } else {
            $ofxSgml = $this->conditionallyAddNewlines($ofxSgml);
            $ofxXml = $this->convertSgmlToXml($ofxSgml);
        }

        $xml = $this->xmlLoadString($ofxXml);

        $ofx = $this->createOfx($xml);
        $ofx->buildHeader($header);

        return $ofx;
    }

    /**
     * Detect if the OFX file is on one line. If it is, add newlines automatically.
     *
     * @param string $ofxContent
     * @return string
     */
    private function conditionallyAddNewlines($ofxContent)
    {
        if (preg_match('/<OFX>.*<\/OFX>/', $ofxContent) === 1) {
            return str_replace('<', "\n<", $ofxContent); // add line breaks to allow XML to parse
        }

        return $ofxContent;
    }

    /**
     * Load an XML string without PHP errors - throws exception instead
     *
     * @param string $xmlString
     * @throws \RuntimeException
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
        // Special case discovered where empty content tag wasn't closed
        $line = trim($line);
        if (preg_match('/<MEMO>$/', $line) === 1) {
            return '<MEMO></MEMO>';
        }

        // Matches: <SOMETHING>blah
        // Does not match: <SOMETHING>
        // Does not match: <SOMETHING>blah</SOMETHING>
        if (preg_match(
            "/<([A-Za-z0-9.]+)>([\wà-úÀ-Ú0-9\.\-\_\+\, ;:\[\]\'\&\/\\\*\(\)\+\{\|\}\!\£\$\?=@€£#%±§~`\"]+)$/",
            $line,
            $matches
        )) {
            return "<{$matches[1]}>{$matches[2]}</{$matches[1]}>";
        }
        return $line;
    }

    /**
     * Parse the SGML Header to an Array
     *
     * @param string $ofxHeader
     * @param int $sgmlStart
     * @return array
     */
    private function parseHeader($ofxHeader)
    {
        $header = [];


        $ofxHeader = trim($ofxHeader);
        // Remove empty new lines.
        $ofxHeader = preg_replace('/^\n+/m', '', $ofxHeader);

        // Check if it's an XML file (OFXv2)
        if(preg_match('/^<\?xml/', $ofxHeader) === 1) {
            // Only parse OFX headers and not XML headers.
            $ofxHeader = preg_replace('/<\?xml .*?\?>\n?/', '', $ofxHeader);
            $ofxHeader = preg_replace(['/"/', '/\?>/', '/<\?OFX/i'], '', $ofxHeader);
            $ofxHeaderLine = explode(' ', trim($ofxHeader));

            foreach ($ofxHeaderLine as $value) {
                $tag = explode('=', $value);
                $header[$tag[0]] = $tag[1];
            }

            return $header;
        }

        $ofxHeaderLines = explode("\n", $ofxHeader);
        foreach ($ofxHeaderLines as $value) {
            $tag = explode(':', $value);
            $header[$tag[0]] = $tag[1];
        }

        return $header;
    }

    /**
     * Convert an SGML to an XML string
     *
     * @param string $sgml
     * @return string
     */
    private function convertSgmlToXml($sgml)
    {
        $sgml = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $sgml);

        $lines = explode("\n", $sgml);
        $tags = [];

        foreach ($lines as $i => &$line) {
            $line = trim($this->closeUnclosedXmlTags($line)) . "\n";

            // Matches tags like <SOMETHING> or </SOMETHING>
            if (!preg_match("/^<(\/?[A-Za-z0-9.]+)>$/", trim($line), $matches)) {
                continue;
            }

            // If matches </SOMETHING>, looks back and replaces all tags like
            // <OTHERTHING> to <OTHERTHING/> until finds the opening tag <SOMETHING>
            if ($matches[1][0] == '/') {
                $tag = substr($matches[1], 1);

                while (($last = array_pop($tags)) && $last[1] != $tag) {
                    $lines[$last[0]] = "<{$last[1]}/>";
                }
            } else {
                $tags[] = [$i, $matches[1]];
            }
        }

        return implode("\n", array_map('trim', $lines));
    }
}
