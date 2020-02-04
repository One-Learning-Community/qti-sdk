<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\TextRun;
use qtism\data\content\interactions\Prompt;
use DOMDocument;

class PromptMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new Prompt('my-prompt', 'qti-prompt');
        $component->setContent(new FlowStaticCollection(array(new TextRun('This is a prompt'))));
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<prompt id="my-prompt" class="qti-prompt">This is a prompt</prompt>', $dom->saveXML($element));
    }
    
    public function testUnmarshall()
    {
        $element = $this->createDOMElement('<prompt id="my-prompt" class="qti-prompt">This is a prompt</prompt>');
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\Prompt', $component);
        $this->assertEquals('my-prompt', $component->getId());
        $this->assertEquals('qti-prompt', $component->getClass());
        
        $content = $component->getContent();
        $this->assertEquals(1, count($content));
        $this->assertEquals('This is a prompt', $content[0]->getContent());
    }
    
    public function testUnmarshallExcludedFlowStatic()
    {
        $element = $this->createDOMElement('<prompt id="my-prompt" class="qti-prompt">This is a prompt with a <pre>pre which is not allowed.</pre></prompt>');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "A 'prompt' cannot contain 'pre' elements."
        );
        
        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    public function testUnmarshallExcludedComponents()
    {
        $element = $this->createDOMElement('
            <prompt id="my-prompt" class="qti-prompt">
                This is a prompt containing a choice interaction.
                <choiceInteraction responseIdentifier="RESPONSE">
                    <simpleChoice identifier="choice">Choice</simpleChoice>
                </choiceInteraction>
            </prompt>');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "A 'prompt' cannot contain 'choiceInteraction' elements."
        );
        
        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
