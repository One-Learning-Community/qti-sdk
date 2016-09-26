<?php
namespace qtismtest\runtime\rendering\markup\goldilocks;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\goldilocks\GoldilocksRenderingEngine;

class GoldilocksRenderingEngineTest extends QtiSmTestCase {
	
    /**
     * @dataProvider testRenderingProvider
     */
    public function testRendering($file, $expectedFile, $renderingMode, $xmlBasePolicy, $stylesheetPolicy, $cssClassPolicy) 
    {
        $engine = new GoldilocksRenderingEngine();
        $engine->setChoiceShowHidePolicy($renderingMode);
        $engine->setFeedbackShowHidePolicy($renderingMode);
        $engine->setViewPolicy($renderingMode);
        $engine->setPrintedVariablePolicy($renderingMode);
        $engine->setXmlBasePolicy($xmlBasePolicy);
        $engine->setStylesheetPolicy($stylesheetPolicy);
        $engine->setCssClassPolicy($cssClassPolicy);
        
        $doc = new XmlDocument();
        $doc->load($file);
        
        $rendered = $engine->render($doc->getDocumentComponent());
        $strRendered = $rendered->saveXML($rendered->documentElement);
        $strExpected = rtrim(file_get_contents($expectedFile));
        $this->assertEquals($strExpected, $strRendered);
    }
    
    public function testRenderingProvider()
    {
        return array(
            // choiceInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/choice.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-1
            array(
                self::samplesDir() . 'ims/items/2_2/choice_multiple.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-1.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-2
            array(
                self::samplesDir() . 'ims/items/2_2/choice_fixed.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-2.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-3
            array(
                self::samplesDir() . 'ims/items/2_2/choice_multiple_rtl.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-3.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-4
            array(
                self::samplesDir() . 'ims/items/2_2/likert.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-4.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-5
            array(
                self::samplesDir() . 'ims/items/2_2/orkney1.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-5.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-6
            array(
                self::samplesDir() . 'ims/items/2_2/orkney2.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-6.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // associateInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/associate.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/associateInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // extendedTextInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/extended_text.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/extendedTextInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // gapMatchInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/gap_match.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/gapMatchInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // graphicAssociateInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/graphic_associate.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/graphicAssociateInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // graphicGapMatchInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/graphic_gap_match.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/graphicGapMatchInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // hotspotInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/hotspot.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/hotspotInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // hottextInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/hottext.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/hottextInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // matchInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/match.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/matchInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // math-0
            array(
                self::samplesDir() . 'ims/items/2_2/math.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/math-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // nestedObject-0
            array(
                self::samplesDir() . 'ims/items/2_2/nested_object.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/nestedObject-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // orderInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/order.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/orderInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // orderInteraction-1
            array(
                self::samplesDir() . 'ims/items/2_2/order_rtl.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/orderInteraction-1.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // positionObjectInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/position_object.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/positionObjectInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // selectPointInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/select_point.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/selectPointInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // sliderInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/slider.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/sliderInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // textEntryInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/text_entry.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/textEntryInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // mediaInteraction-0
            array(
                self::samplesDir() . 'custom/items/media_audio.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/mediaInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // mediaInteraction-1
            array(
                self::samplesDir() . 'custom/items/media_video.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/mediaInteraction-1.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // mediaInteraction-2
            array(
                self::samplesDir() . 'custom/items/media_image.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/mediaInteraction-2.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
        );
    }
    
    /*public function testGenerate() {
        $renderingMode = GoldilocksRenderingEngine::CONTEXT_STATIC;
        $xmlBasePolicy = GoldilocksRenderingEngine::XMLBASE_IGNORE;
        $stylesheetPolicy = GoldilocksRenderingEngine::STYLESHEET_INLINE;
        $cssClassPolicy = GoldilocksRenderingEngine::CSSCLASS_CONCRETE;
        
        $engine = new GoldilocksRenderingEngine();
        $engine->setChoiceShowHidePolicy($renderingMode);
        $engine->setFeedbackShowHidePolicy($renderingMode);
        $engine->setViewPolicy($renderingMode);
        $engine->setPrintedVariablePolicy($renderingMode);
        $engine->setXmlBasePolicy($xmlBasePolicy);
        $engine->setStylesheetPolicy($stylesheetPolicy);
        $engine->setCssClassPolicy($cssClassPolicy);
        
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/media_image.xml');
        
        $rendered = $engine->render($doc->getDocumentComponent());
        $strRendered = $rendered->saveXML($rendered->documentElement);
        file_put_contents(self::samplesDir() . 'rendering/goldilocks/rendered/mediaInteraction-2.html', $strRendered . "\n");
    }*/
}
