<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\expressions\MapResponsePoint;
use DOMDocument;

class MapResponsePointMarshallerTest extends QtiSmTestCase
{

    public function testMarshall()
    {

        $identifier = 'myMapResponsePoint1';
        
        $component = new MapResponsePoint($identifier);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);
        
        $this->assertInstanceOf('\\DOMElement', $element);
        $this->assertEquals('mapResponsePoint', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
    }
    
    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<mapResponsePoint xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myMapResponsePoint1"/>');
        $element = $dom->documentElement;
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\expressions\\MapResponsePoint', $component);
        $this->assertEquals($component->getIdentifier(), 'myMapResponsePoint1');
    }
}
