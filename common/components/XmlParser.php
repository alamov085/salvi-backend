<?php

namespace common\components;

use XMLReader;
use SimpleXMLElement;
use InvalidArgumentException;

/**
 * This class allows to traverse an XML file in efficient way.
 *
 * @package Util\XML
 *
 */
class XmlParser
{
    /**
     * @property XMLReader $reader
     */
    private $reader;

    /**
     * Create a new instance of the Reader
     *
     * @param string $file The XML file full path
     */
    public function __construct($file)
    {
        if (!is_readable($file)) {
            throw new InvalidArgumentException("Failed to open file:
                {$file} Permission denied " . __CLASS__ . ' ' .
                __FUNCTION__ . ' method');
        }

        $this->reader = new XMLReader();
        // Open the reader
        $this->reader->open($file, LIBXML_NOBLANKS | LIBXML_COMPACT);
    }

    /**
     * Get an XML representation from an XML node using XMLReader, Generator and SimpleXMLElement
     *
     * @param string $path node XPath
     * @return \Generator
     */
    public function read($path)
    {
        if (empty($path)) {
            throw new InvalidArgumentException("Node path can't be empty" .
                __CLASS__ . ' ' .
                __FUNCTION__ . ' method');
        }

        // Set the path traversed by the reader
        $pathNode = '';
        // Start to read from the first node
        while ($this->reader->read()) {
            // Name and type of the current node
            $nodeName = $this->reader->name;
            $nodeType = $this->reader->nodeType;
            /**
             * Checks if the node is a "start element"
             * @see https://secure.php.net/manual/es/class.xmlreader.php
             */
            if (XMLReader::ELEMENT == $nodeType) {
                if (empty($pathNode)) {
                    $pathNode = $nodeName;
                } else {
                    $newPath = implode('/', [$pathNode, $nodeName]);
                    // Add the name of the node to the traversed path
                    if (false !== strpos($path, $newPath)) {
                        $pathNode = $newPath;
                    }
                }
                // Compare traversed path with current node
                if ($pathNode == $path) {
                    // Delete the node name from the traversed path
                    $pathNode = preg_replace("/\/?{$nodeName}$/", '', $pathNode);
                    /**
                     * Get the XML string representation from the found node, node tags are included,
                     * SimpleXMLElement object is created and returns a Generator
                     */
                    yield (new SimpleXMLElement($this->reader->readOuterXML()));
                }
            }
        }
    }
}