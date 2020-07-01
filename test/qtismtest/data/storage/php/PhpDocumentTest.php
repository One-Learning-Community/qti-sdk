<?php

namespace qtismtest\data\storage\php;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\content\xhtml\text\Span;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Equal;
use qtism\data\expressions\operators\ToleranceMode;
use qtism\data\NavigationMode;
use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\php\PhpStorageException;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\SubmissionMode;
use qtismtest\QtiSmTestCase;

class PhpDocumentTest extends QtiSmTestCase
{
    public function testSimpleLoad($path = '')
    {
        $doc = new PhpDocument();
        if (empty($path) === true) {
            $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.php');
        } else {
            $doc->load($path);
        }

        $assessmentTest = $doc->getDocumentComponent();
        $this->assertInstanceOf('qtism\\data\\AssessmentTest', $assessmentTest);
        $this->assertEquals('php_storage_simple', $assessmentTest->getIdentifier());
        $this->assertEquals('PHP Storage Simple', $assessmentTest->getTitle());

        $testParts = $assessmentTest->getTestParts();
        $this->assertEquals(1, count($testParts));
        $this->assertTrue(isset($testParts['P01']));
        $this->assertEquals('P01', $testParts['P01']->getIdentifier());
        $this->assertEquals(NavigationMode::LINEAR, $testParts['P01']->getNavigationMode());
        $this->assertEquals(SubmissionMode::INDIVIDUAL, $testParts['P01']->getSubmissionMode());

        $assessmentSections = $testParts['P01']->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S01']));
        $this->assertEquals('S01', $assessmentSections['S01']->getIdentifier());
        $this->assertEquals('Section1', $assessmentSections['S01']->getTitle());
        $this->assertTrue($assessmentSections['S01']->isVisible());

        $assessmentItemRefs = $assessmentSections['S01']->getSectionParts();
        $this->assertEquals(3, count($assessmentItemRefs));
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $assessmentItemRefs['Q01']);
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $assessmentItemRefs['Q02']);
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $assessmentItemRefs['Q03']);

        $this->assertEquals('Q01', $assessmentItemRefs['Q01']->getIdentifier());
        $this->assertEquals('./Q01.xml', $assessmentItemRefs['Q01']->getHref());
        $this->assertFalse(false, $assessmentItemRefs['Q01']->isTimeDependent());
        $this->assertEquals(['mathematics', 'chemistry'], $assessmentItemRefs['Q01']->getCategories()->getArrayCopy());
        $variableMappings = $assessmentItemRefs['Q01']->getVariableMappings();
        $this->assertEquals(1, count($variableMappings));
        $this->assertEquals('scoring', $variableMappings[0]->getSource());
        $this->assertEquals('SCORE', $variableMappings[0]->getTarget());
        $weights = $assessmentItemRefs['Q01']->getWeights();
        $this->assertEquals(1, count($weights));
        $this->assertEquals('W01', $weights['W01']->getIdentifier());
        $this->assertEquals(2.0, $weights['W01']->getValue());
        $responseDeclarations = $assessmentItemRefs['Q01']->getResponseDeclarations();
        $this->assertEquals(1, count($responseDeclarations));
        $this->assertEquals('RESPONSE', $responseDeclarations['RESPONSE']->getIdentifier());
        $this->assertEquals(Cardinality::SINGLE, $responseDeclarations['RESPONSE']->getCardinality());
        $this->assertEquals(BaseType::IDENTIFIER, $responseDeclarations['RESPONSE']->getBaseType());
        $values = $responseDeclarations['RESPONSE']->getCorrectResponse()->getValues();
        $this->assertEquals(1, count($values));
        $this->assertEquals('ChoiceA', $values[0]->getValue());
        $outcomeDeclarations = $assessmentItemRefs['Q01']->getOutcomeDeclarations();
        $this->assertEquals(1, count($outcomeDeclarations));
        $this->assertEquals('scoring', $outcomeDeclarations['scoring']->getIdentifier());
        $this->assertEquals(Cardinality::SINGLE, $outcomeDeclarations['scoring']->getCardinality());
        $this->assertEquals(BaseType::FLOAT, $outcomeDeclarations['scoring']->getBaseType());
        $values = $outcomeDeclarations['scoring']->getDefaultValue()->getValues();
        $this->assertEquals(0.0, $values[0]->getValue());
        $responseProcessing = $assessmentItemRefs['Q01']->getResponseProcessing();
        $this->assertInstanceOf('qtism\\data\\processing\\ResponseProcessing', $responseProcessing);
        $this->assertFalse($responseProcessing->hasTemplateLocation());
        $this->assertFalse($responseProcessing->hasTemplate());
        $responseRules = $responseProcessing->getResponseRules();
        $this->assertEquals(1, count($responseRules));
    }

    public function testSimpleSave()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.xml');
        $phpDoc = new PhpDocument('2.1', $doc->getDocumentComponent());
        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $this->testSimpleLoad($file);

        unlink($file);
    }

    public function testCustomOperatorOne()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/operators/custom_operator_1.xml');
        $phpDoc = new PhpDocument('2.1', $doc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $customOperator = $phpDoc->getDocumentComponent();
        $xml = $customOperator->getXml();
        $this->assertInstanceOf('qtism\\data\\expressions\\operators\\CustomOperator', $customOperator);
        $this->assertEquals('com.taotesting.qtism.customOperator1', $customOperator->getClass());
        $this->assertEquals('http://qtism.taotesting.com/xsd/customOperator1.xsd', $customOperator->getDefinition());
        $this->assertEquals('false', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'debug'));
        $this->assertEquals('default', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'syntax'));
        $this->assertEquals('<customOperator xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:qtism="http://qtism.taotesting.com" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd" class="com.taotesting.qtism.customOperator1" definition="http://qtism.taotesting.com/xsd/customOperator1.xsd" qtism:debug="false" qtism:syntax="default">
    <baseValue baseType="string"><![CDATA[Param1Data]]></baseValue>
</customOperator>', $xml->saveXML($xml->documentElement));

        unlink($file);
    }

    public function testCustomOperatorTwo()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/operators/custom_operator_2.xml');
        $phpDoc = new PhpDocument('2.1', $doc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $customOperator = $phpDoc->getDocumentComponent();
        $xml = $customOperator->getXml();
        $this->assertInstanceOf('qtism\\data\\expressions\\operators\\CustomOperator', $customOperator);
        $this->assertEquals('<customOperator xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd">
    <baseValue baseType="string"><![CDATA[Param1Data]]></baseValue>
</customOperator>', $xml->saveXML($xml->documentElement));

        unlink($file);
    }

    public function testCustomSelection()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/selection/custom_selection.xml');
        $test = $doc->getDocumentComponent();
        $phpDoc = new PhpDocument('2.1', $test);

        $selection = $test->getComponentsByClassName('selection')[0];
        $this->assertInstanceOf('qtism\data\rules\Selection', $selection);

        $domSelection = $selection->getXml();
        $this->assertNotNull($domSelection);

        $this->assertEquals(1, $domSelection->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveItemSelection')->length);
        $this->assertEquals(1, $domSelection->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveEngineRef')->length);

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        // Do we have the same result after opening it again?
        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $test = $phpDoc->getDocumentComponent();

        $selection = $test->getComponentsByClassName('selection')[0];
        $this->assertInstanceOf('qtism\data\rules\Selection', $selection);

        $domSelection = $selection->getXml();
        $this->assertNotNull($domSelection);

        $this->assertEquals(1, $domSelection->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveItemSelection')->length);
        $this->assertEquals(1, $domSelection->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveEngineRef')->length);

        unlink($file);
    }

    /**
     *
     * @dataProvider loadTestSamplesDataProvider
     * @param string $testUri
     * @param string $rootType The expected fully qualified class name of the document component.
     */
    public function testLoadTestSamples($testUri, $rootType)
    {
        // Basic XML -> PHP transormation + save + load
        $xmlDoc = new XmlDocument('2.1');
        $xmlDoc->load($testUri);

        $phpDoc = new PhpDocument();
        $phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $this->assertInstanceOf($rootType, $phpDoc->getDocumentComponent());
        $this->assertEquals($file, $phpDoc->getUrl());

        unlink($file);
        $this->assertFalse(file_exists($file));
    }

    public function testLoadInteractionMixSaschsen()
    {
        $xmlDoc = new XmlDocument('2.1');
        $xmlDoc->load(self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml');

        $phpDoc = new PhpDocument();
        $phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $this->assertEquals('InteractionMixSachsen_1901710679', $phpDoc->getDocumentComponent()->getIdentifier());
        unlink($file);
        $this->assertFalse(file_exists($file));
    }

    public function loadTestSamplesDataProvider()
    {
        return [
            [self::samplesDir() . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/arbitrary_weighting_of_item_outcomes/arbitrary_weighting_of_item_outcomes.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/basic_statistics_as_outcomes/basic_statistics_as_outcomes.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/branching_based_on_the_response_to_an_assessmentitem/branching_based_on_the_response_to_an_assessmentitem.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/controlling_the_duration_of_an_item_attempt/controlling_the_duration_of_an_item_attempt.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/controlling_item_feedback_in_relation_to_the_test/controlling_item_feedback_in_relation_to_the_test.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/early_termination_of_test_based_on_accumulated_item_outcomes/early_termination_of_test_based_on_accumulated_item_outcomes.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/feedback_examples_test/feedback_examples_test.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/golden_required_items_and_sections/golden_required_items_and_sections.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/items_arranged_into_sections_within_tests/items_arranged_into_sections_within_tests.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/mapping_item_outcomes_prior_to_aggregation/mapping_item_outcomes_prior_to_aggregation.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/randomizing_the_order_of_items_and_sections/randomizing_the_order_of_items_and_sections.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/sets_of_items_with_leading_material/sets_of_items_with_leading_material.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/simple_feedback_test/simple_feedback_test.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'ims/tests/specifiying_the_number_of_allowed_attempts/specifiying_the_number_of_allowed_attempts.xml', 'qtism\\data\\AssessmentTest'],
            [self::samplesDir() . 'rendering/various_content.xml', 'qtism\\data\\content\\RubricBlock'],
            [self::samplesDir() . 'rendering/associateinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/choiceinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/choiceinteraction_2.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/drawinginteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/endattemptinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/extendedtextinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/gapmatchinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/graphicgapmatchinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/graphicorderinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/hotspotinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/hottextinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/inlinechoiceinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/itembodywithfeedback_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/matchinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/mediainteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/mediainteraction_2.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/mediainteraction_3.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/orderinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/selectpointinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/positionobjectinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/sliderinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/textentryinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/uploadinteraction_1.xml', 'qtism\\data\\content\\ItemBody'],
            [self::samplesDir() . 'rendering/itemfeedback_1.xml', 'qtism\\data\\AssessmentItem'],
            [self::samplesDir() . 'rendering/empty_object.xml', 'qtism\\data\\content\\xhtml\\ObjectElement'],
            [self::samplesDir() . 'rendering/empty_rubricblock.xml', 'qtism\\data\\content\\RubricBlock'],
            [self::samplesDir() . 'rendering/rubricblock_1.xml', 'qtism\\data\\content\\RubricBlock'],
            [self::samplesDir() . 'rendering/rubricblock_2.xml', 'qtism\\data\\content\\RubricBlock'],
            [self::samplesDir() . 'rendering/rubricblock_3.xml', 'qtism\\data\\content\\RubricBlock'],
            [self::samplesDir() . 'rendering/math_1.xml', 'qtism\\data\\AssessmentItem'],
            [self::samplesDir() . 'rendering/math_2.xml', 'qtism\\data\\AssessmentItem'],
            [self::samplesDir() . 'rendering/math_3.xml', 'qtism\\data\\AssessmentItem'],
            [self::samplesDir() . 'rendering/math_4.xml', 'qtism\\data\\Content\\RubricBlock'],
            [self::samplesDir() . 'custom/operators/custom_operator_1.xml', 'qtism\\data\\expressions\\operators\\CustomOperator'],
            [self::samplesDir() . 'custom/operators/custom_operator_2.xml', 'qtism\\data\\expressions\\operators\\CustomOperator'],
            [self::samplesDir() . 'custom/operators/custom_operator_3.xml', 'qtism\\data\\expressions\\operators\\CustomOperator'],
            [self::samplesDir() . 'custom/operators/custom_operator_nested_1.xml', 'qtism\\data\\expressions\\operators\\CustomOperator'],
            [self::samplesDir() . 'custom/interactions/custom_interaction_pci.xml', 'qtism\\data\\AssessmentItem'],
        ];
    }

    public function testSaveComponentWithArrayBeanProperty()
    {
        $equal = new Equal(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::FLOAT, 2.22),
                    new BaseValue(BaseType::FLOAT, 2.22),
                ]
            ),
            ToleranceMode::RELATIVE,
            [5, 5]
        );

        $file = tempnam('/tmp', 'qsm');
        $phpDoc = new PhpDocument('2.1', $equal);
        $phpDoc->save($file);

        $phpDoc2 = new PhpDocument('2.1');
        $phpDoc2->load($file);

        $this->assertInstanceOf('qtism\\data\\expressions\\operators\\Equal', $phpDoc2->getDocumentComponent());
        $this->assertEquals(ToleranceMode::RELATIVE, $phpDoc2->getDocumentComponent()->getToleranceMode());
        $this->assertEquals([5, 5], $phpDoc2->getDocumentComponent()->getTolerance());

        unlink($file);
    }

    public function testSaveError()
    {
        $phpDoc = new PhpDocument();

        $this->setExpectedException(
            'qtism\\data\\storage\\php\\PhpStorageException',
            "File located at '/root/root.php' could not be written."
        );

        $phpDoc->save('/root/root.php');
    }

    public function testLoadError()
    {
        $phpDoc = new PhpDocument();

        $this->setExpectedException(
            'qtism\\data\\storage\\php\\PhpStorageException',
            "The PHP document located at '/root/root.php' is not readable or does not exist."
        );

        $phpDoc->load('/root/root.php');
    }

    /**
     * @throws PhpStorageException
     */
    public function testBodyElement()
    {
        $span = new Span('myid', 'myclass');
        $span->setAriaControls('IDREF1 IDREF2');
        $span->setAriaDescribedBy('IDREF3');
        $span->setAriaFlowTo('IDREF4');
        $span->setAriaLabel('My Label');
        $span->setAriaLabelledBy('IDREF5');
        $span->setAriaLevel(5);
        $span->setAriaLive('off');
        $span->setAriaOrientation(AriaOrientation::VERTICAL);
        $span->setAriaOwns('IDREF6');

        $file = tempnam('/tmp', 'qsm');
        $phpDoc = new PhpDocument('2.2', $span);
        $phpDoc->save($file);

        $phpDoc2 = new PhpDocument('2.2');
        $phpDoc2->load($file);
        unlink($file);

        /** @var Span $span */
        $span = $phpDoc2->getDocumentComponent();

        $this->assertEquals('myid', $span->getId());
        $this->assertEquals('myclass', $span->getClass());
        $this->assertEquals('IDREF1 IDREF2', $span->getAriaControls());
        $this->assertEquals('IDREF3', $span->getAriaDescribedBy());
        $this->assertEquals('IDREF4', $span->getAriaFlowTo());
        $this->assertEquals('My Label', $span->getAriaLabel());
        $this->assertEquals('IDREF5', $span->getAriaLabelledBy());
        $this->assertEquals('5', $span->getAriaLevel());
        $this->assertEquals('off', $span->getAriaLive());
        $this->assertEquals(AriaOrientation::VERTICAL, $span->getAriaOrientation());
        $this->assertEquals('IDREF6', $span->getAriaOwns());
    }
}
