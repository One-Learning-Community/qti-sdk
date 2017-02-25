<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use \qtism\common\utils\Version;
use \DOMDocument;
use \DOMElement;
use \SplStack;

/**
 * A class providing XML utility methods.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Utils
{
    /**
     * Get the XML schema to use for a given QTI version.
     *
     * @return string A filename pointing at an XML Schema file.
     */
    static public function getSchemaLocation($version = '2.1')
    {
        $version = Version::appendPatchVersion($version);
        
        if ($version === '2.1.0') {
            $filename = dirname(__FILE__) . '/schemes/qtiv2p1/imsqti_v2p1.xsd';
        } elseif ($version === '2.1.1') {
            $filename = dirname(__FILE__) . '/schemes/qtiv2p1p1/imsqti_v2p1p1.xsd';
        } elseif ($version === '2.2.0') {
            $filename = dirname(__FILE__) . '/schemes/qtiv2p2/imsqti_v2p2.xsd';
        } elseif ($version === '2.2.1') {
            $filename = dirname(__FILE__) . '/schemes/qtiv2p2p1/imsqti_v2p2p1.xsd';
        } elseif ($version === '3.0.0') {
            $filename = dirname(__FILE__) . '/schemes/aqtiv1p0/imsaqti_itemv1p0_v1p0.xsd';
        } else {
            $filename = dirname(__FILE__) . '/schemes/imsqti_v2p0.xsd';
        }

        return $filename;
    }
    
    /**
     * Infer the QTI version from a given DOM $document in a Semantic Versioning
     * format always containing a MAJOR, MINOR and PATCH version.
     * 
     * @param \DOMDocument $document
     * @return string|boolean A QTI version number if it could be infered, false otherwise. 
     */
    static public function inferVersion(DOMDocument $document)
    {
        $root = $document->documentElement;
        $version = false;
        
        if (empty($root) === false) {
            $rootNs = $root->namespaceURI;
            
            if ($rootNs === 'http://www.imsglobal.org/xsd/imsqti_v2p0') {
                $nsLocation = self::getXsdLocation($document, 'http://www.imsglobal.org/xsd/imsqti_v2p0');
                
                if ($nsLocation === 'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd') {
                    $version = '2.0.0';
                }
            } elseif ($rootNs === 'http://www.imsglobal.org/xsd/imsqti_v2p1') {
                $nsLocation = self::getXsdLocation($document, 'http://www.imsglobal.org/xsd/imsqti_v2p1');
                
                if ($nsLocation === 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd') {
                    $version = '2.1.0';
                } else if ($nsLocation === 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1p1.xsd') {
                    $version = '2.1.1';
                }
            } elseif ($rootNs === 'http://www.imsglobal.org/xsd/imsqti_v2p2') {
                $nsLocation = self::getXsdLocation($document, 'http://www.imsglobal.org/xsd/imsqti_v2p2');
                
                if ($nsLocation === 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_v2p2.xsd') {
                    $version = '2.2.0';
                } elseif ($nsLocation === 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_v2p2p1.xsd') {
                    $version = '2.2.1';
                }
            } elseif ($rootNs === 'http://www.imsglobal.org/xsd/imsaqti_item_v1p0') {
                $nsLocation = self::getXsdLocation($document, 'http://www.imsglobal.org/xsd/imsaqti_item_v1p0');
                
                if ($nsLocation === 'http://www.imsglobal.org/xsd/qti/aqtiv1p0/imsaqti_itemv1p0_v1p0.xsd') {
                    $version = '3.0.0';
                }
            }
        }
        
        return $version;
    }
    
    /**
     * Get the location of an XML Schema Definition file from a given namespace.
     * 
     * This utility method enables you to know what is the location of an XML Schema Definition
     * file to be used to validate a $document for a given target namespace. 
     * 
     * @param DOMDocument $document A DOMDocument object.
     * @param string $namespaceUri A Namespace URI you want to know the related XSD file location.
     * @return boolean|string False if no location can be found for $namespaceUri, otherwise the location of the XSD file.
     */
    static public function getXsdLocation(DOMDocument $document, $namespaceUri)
    {
        $root = $document->documentElement;
        $location = false;
        
        if (empty($root) === false) {
            $schemaLocation = $root->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation');
            if (empty($schemaLocation) === false) {
                $parts = preg_split('/\s+/', $schemaLocation);
                
                // Look at pairs...
                $partsCount = count($parts);
                for ($i = 0; $i < $partsCount; $i += 2) {
                    if (isset($parts[$i + 1]) && $parts[$i] === $namespaceUri) {
                        $location = $parts[$i + 1];
                        break;
                    }
                }
            }
        }
        
        return $location;
    }

    /**
     * Change the name of $element into $name.
     *
     * @param \DOMElement $element A DOMElement object you want to change the name.
     * @param string $name The new name of $element.
     *
     * @return \DOMElement
     */
    static public function changeElementName(DOMElement $element, $name)
    {
        $newElement = $element->ownerDocument->createElement($name);

        foreach ($element->childNodes as $child) {
            $child = $element->ownerDocument->importNode($child, true);
            $newElement->appendChild($child);
        }

        foreach ($element->attributes as $attrName => $attrNode) {
            if ($attrNode->namespaceURI === null) {
                $newElement->setAttribute($attrName, $attrNode->value);
            } else {
                $newElement->setAttributeNS($attrNode->namespaceURI, $attrNode->prefix . ':' . $attrName, $attrNode->value);
            }

        }

        $newElement->ownerDocument->replaceChild($newElement, $element);

        return $newElement;
    }

    /**
     * Anonimize a given DOMElement. By 'anonimize', we mean remove
     * all namespace membership of an element and its child nodes.
     *
     * For instance, <m:math display="inline"><m:mi>x</m:mi></m:math> becomes
     * <math display="inline"><mi>x</mi></math>.
     *
     * @param \DOMElement $element The DOMElement to be anonimized.
     * @return \DOMElement The anonimized DOMElement copy of $element.
     */
    static public function anonimizeElement(DOMElement $element)
    {
        $stack = new SplStack();
        $traversed = array();
        $children = array();

        $stack->push($element);

        while ($stack->count() > 0) {
            $node = $stack->pop();

            if ($node->nodeType === XML_ELEMENT_NODE && $node->childNodes->length > 0 && in_array($node, $traversed, true) === false) {
                array_push($traversed, $node);
                $stack->push($node);

                for ($i = 0; $i < $node->childNodes->length; $i++) {
                    $stack->push($node->childNodes->item($i));
                }
            } elseif ($node->nodeType === XML_ELEMENT_NODE && $node->childNodes->length > 0 && in_array($node, $traversed, true) === true) {
                // Build hierarchical node copy from the current node. All the attributes
                // of $node must be copied into $newNode.
                $newNode = $node->ownerDocument->createElement($node->localName);

                // Copy all attributes.
                foreach ($node->attributes as $attr) {
                    $newNode->setAttribute($attr->localName, $attr->value);
                }

                for ($i = 0; $i < $node->childNodes->length; $i++) {
                    $newNode->appendChild(array_pop($children));
                }

                array_push($children, $newNode);
            } else {
                array_push($children, $node->cloneNode());
            }
        }

        return $children[0];
    }

    /**
     * Import all the child nodes of DOMElement $from to DOMElement $into.
     *
     * @param \DOMElement $from The source DOMElement.
     * @param \DOMElement $into The target DOMElement.
     * @param boolean $deep Whether or not to import the whole node hierarchy.
     */
    static public function importChildNodes(DOMElement $from, DOMElement $into, $deep = true)
    {
        for ($i = 0; $i < $from->childNodes->length; $i++) {
            $node = $into->ownerDocument->importNode($from->childNodes->item($i), $deep);
            $into->appendChild($node);
        }
    }

    /**
     * Import (gracefully i.e. by respecting namespaces) the attributes of DOMElement $from to
     * DOMElement $into.
     *
     * @param \DOMElement $from The source DOMElement.
     * @param \DOMElement $into The target DOMElement.
     */
    static public function importAttributes(DOMElement $from, DOMElement $into)
    {
        for ($i = 0; $i < $from->attributes->length; $i++) {
            $attr = $from->attributes->item($i);

            if ($attr->localName !== 'schemaLocation') {

                if (empty($attr->namespaceURI) === false) {
                    $into->setAttributeNS($attr->namespaceURI, $attr->prefix . ':' . $attr->localName, $attr->value);
                } else {
                    $into->setAttribute($attr->localName, $attr->value);
                }
            }
        }
    }
    
    /**
     * Escape XML Special characters from a given string.
     * 
     * The list below describe each escaped character and its replacement.
     * 
     * * " --> &quot;
     * * ' --> &apos;
     * * < --> &lt;
     * * > --> $gt;
     * * & --> &amp;
     * 
     * @param string $string An input string.
     * @param boolean $isAttribute Whether or not to escape ', >, < which do not have to be escaped in attributes.
     * @return string An escaped string.
     */
    static public function escapeXmlSpecialChars($string, $isAttribute = false)
    {
        if ($isAttribute === false) {
            $fullSearch = array('"', "'", '<', '>');
            $fullReplace = array('&quot;', '&apos;', '&lt;', '&gt;');
            $string = str_replace('&', '&amp;', $string);
            $string = str_replace($fullSearch, $fullReplace, $string);
            return $string;
        } else {
            $string = str_replace('&', '&amp;', $string);
            $string = str_replace('"', '&quot;', $string);
            return $string;
        }
    }
    
    /**
     * Web Component friendly version of a QTI name (without qti-).
     * 
     * This method returns the Web Component friendly version of a QTI attribute name.
     * 
     * Example: "minChoices" becomes "min-choices".
     * 
     * @param string $qtiName
     * @return string
     */
    static public function webComponentFriendlyAttributeName($qtiName)
    {
        return strtolower(preg_replace('/([A-Z])/', '-$1', $qtiName));
    }
    
    /**
     * Web Component friendly version of a QTI name (with qti-).
     * 
     * This method returns the Web Component friendly version of a QTI class name.
     * 
     * Example: "choiceInteraction" becomes "qti-choice-interaction".
     * 
     * @param string $qtiName
     * @return string
     */
    static public function webComponentFriendlyClassName($qtiName)
    {
        return 'qti-' . self::webComponentFriendlyAttributeName($qtiName);
    }
    
    /**
     * QTI friendly name of a Web Component friendly name.
     * 
     * This method returns the QTI friendly name of a Web Component friendly name.
     * 
     * Example: "qti-choice-interaction" becomes "choiceInteraction".
     * 
     * @param string $wcName
     * @return string
     */
    static public function qtiFriendlyName($wcName)
    {
        $qtiName = strtolower($wcName);
        $qtiName = preg_replace('/^qti-/', '', $qtiName);
        
        return lcfirst(str_replace('-', '', ucwords($qtiName, '-')));
    }
    
    /**
     * Get the attribute value of a given DOMElement object, cast in a given datatype.
     *
     * @param DOMElement $element The element the attribute you want to retrieve the value is bound to.
     * @param string $attribute The attribute name.
     * @param string $datatype The returned datatype. Accepted values are 'string', 'integer', 'float', 'double' and 'boolean'.
     * @throws \InvalidArgumentException If $datatype is not in the range of possible values.
     * @return mixed The attribute value with the provided $datatype, or null if the attribute does not exist in $element.
     */
    public static function getDOMElementAttributeAs(DOMElement $element, $attribute, $datatype = 'string')
    {
        $attr = $element->getAttribute($attribute);
    
        if ($attr !== '') {
            switch ($datatype) {
                case 'string':
                    return $attr;
                    break;
            
                case 'integer':
                    return intval($attr);
                    break;
            
                case 'float':
                    return floatval($attr);
                    break;
            
                case 'double':
                    return doubleval($attr);
                    break;
            
                case 'boolean':
                    return ($attr == 'true') ? true : false;
                    break;
            
                default:
                    throw new \InvalidArgumentException("Unknown datatype '${datatype}'.");
                    break;
            }
        } else {
            return null;
        }
    }
    
    /**
     * Set the attribute value of a given DOMElement object. Boolean values will be transformed
     *
     * @param DOMElement $element A DOMElement object.
     * @param string $attribute An XML attribute name.
     * @param mixed $value A given value.
     */
    public static function setDOMElementAttribute(DOMElement $element, $attribute, $value)
    {
        switch (gettype($value)) {
            case 'boolean':
                $element->setAttribute($attribute, ($value === true) ? 'true' : 'false');
                break;
            
            default:
                $element->setAttribute($attribute, '' . $value);
                break;
        }
    }
    
    /**
     * Get the child elements of a given element by tag name. This method does
     * not behave like DOMElement::getElementsByTagName. It only returns the direct
     * child elements that matches $tagName but does not go recursive.
     *
     * @param DOMElement $element A DOMElement object.
     * @param mixed $tagName The name of the tags you would like to retrieve or an array of tags to match.
     * @param boolean $exclude (optional) Wether the $tagName parameter must be considered as a blacklist.
     * @param boolean $withText (optional) Wether text nodes must be returned or not.
     * @return array An array of DOMElement objects.
     */
    public static function getChildElementsByTagName($element, $tagName, $exclude = false, $withText = false)
    {
        if (!is_array($tagName)) {
            $tagName = array($tagName);
        }
        
        $rawElts = self::getChildElements($element, $withText);
        $returnValue = array();
        
        foreach ($rawElts as $elt) {
            if (in_array($elt->localName, $tagName) === !$exclude) {
                $returnValue[] = $elt;
            }
        }
        
        return $returnValue;
    }
    
    /**
     * Get the children DOM Nodes with nodeType attribute equals to XML_ELEMENT_NODE.
     *
     * @param DOMElement $element A DOMElement object.
     * @param boolean $withText Wether text nodes must be returned or not.
     * @return array An array of DOMNode objects.
     */
    public static function getChildElements($element, $withText = false)
    {
        $children = $element->childNodes;
        $returnValue = array();
        
        for ($i = 0; $i < $children->length; $i++) {
            if ($children->item($i)->nodeType === XML_ELEMENT_NODE || ($withText === true && ($children->item($i)->nodeType === XML_TEXT_NODE || $children->item($i)->nodeType === XML_CDATA_SECTION_NODE))) {
                $returnValue[] = $children->item($i);
            }
        }
        
        return $returnValue;
    }
}
