<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\SectionPart;
use DOMDocument;

class SectionPartMarshallerTest extends QtiSmTestCase
{

    public function testMarshallMinimal()
    {

        $identifier = 'mySectionPart1';
        
        $component = new SectionPart($identifier);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);
        
        $this->assertInstanceOf('\\DOMElement', $element);
        $this->assertEquals('sectionPart', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
    }
    
    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<sectionPart xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="mySectionPart1"/>');
        $element = $dom->documentElement;
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\SectionPart', $component);
        $this->assertEquals('mySectionPart1', $component->getIdentifier());
        $this->assertFalse($component->isFixed());
        $this->assertFalse($component->isRequired());
    }
}
