<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 21.11.2019
 * Time: 11:20
 */

namespace common\helpers\xml;

/**
 * Class SplitXml
 * @package common\helpers\xml
 */
class SplitXml
{
    public static function processChunk($lastChunk = false)
    {
        global $CHUNKS, $PAYLOAD, $ITEMCOUNT;
        if ('' == $PAYLOAD) {
            return;
        }
        $xp = fopen($file = "output-$CHUNKS.xml", "w");
        fwrite($xp, '<?xml version="1.0"?>' . "\n");
        fwrite($xp, "<root>");
        fwrite($xp, $PAYLOAD);
        $lastChunk || fwrite($xp, "</root>");
        fclose($xp);
        print "Written {$file}\n";
        $CHUNKS++;
        $PAYLOAD = '';
        $ITEMCOUNT = 0;
    }

    public static function startElement($xml, $tag, $attrs = array())
    {
        global $PAYLOAD, $CHUNKS, $ITEMCOUNT, $CHUNKON;
        if (!($CHUNKS || $ITEMCOUNT)) {
            if ($CHUNKON == strtolower($tag)) {
                $PAYLOAD = '';
            }
        }
        $PAYLOAD .= "<{$tag}";
        foreach ($attrs as $k => $v) {
            $PAYLOAD .= " {$k}=\"" . addslashes($v) . '"';
        }
        $PAYLOAD .= '>';
    }

    public static function endElement($xml, $tag)
    {
        global $CHUNKON, $ITEMCOUNT, $ITEMLIMIT;
        self::dataHandler(null, "</{$tag}>");
        if ($CHUNKON == strtolower($tag)) {
            if (++$ITEMCOUNT >= $ITEMLIMIT) {
                self::processChunk();
            }
        }
    }

    public static function dataHandler($xml, $data)
    {
        global $PAYLOAD;
        $PAYLOAD .= $data;
    }

    public static function defaultHandler($xml, $data)
    {
        // a.k.a. Wild Text Fallback Handler, or WTFHandler for short.
    }

    public function createXMLParser($CHARSET, $bareXML = false)
    {
        $CURRXML = xml_parser_create($CHARSET);
        xml_parser_set_option($CURRXML, XML_OPTION_CASE_FOLDING, false);
        xml_parser_set_option($CURRXML, XML_OPTION_TARGET_ENCODING, $CHARSET);
        xml_set_element_handler($CURRXML, 'self::startElement', 'self::endElement');
        xml_set_character_data_handler($CURRXML, 'self::dataHandler');
        xml_set_default_handler($CURRXML, 'self::defaultHandler');
        if ($bareXML) {
            xml_parse($CURRXML, '<?xml version="1.0"?>', 0);
        }
        return $CURRXML;
    }

    public function chunkXMLBigFile($file, $tag = 'item', $howmany = 5)
    {
        global $CHUNKON, $CHUNKS, $ITEMLIMIT;

        // Every chunk only holds $ITEMLIMIT "$CHUNKON" elements at most.
        $CHUNKON = $tag;
        $ITEMLIMIT = $howmany;

        $xml = $this->createXMLParser('UTF-8', false);

        $fp = fopen($file, 'r');
        $CHUNKS = 0;
        while (!feof($fp)) {
            $chunk = fgets($fp, 4096);
            xml_parse($xml, $chunk, feof($fp));
        }
        xml_parser_free($xml);

        // Now, it is possible that one last chunk is still queued for processing.
        self::processChunk(true);
    }
}
