<?php

namespace qtismtest\data\storage\xml;

use League\Flysystem\Filesystem;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;

class XmlAssessmentTestDocumentTest extends QtiSmTestCase
{
    
    public function testLoad()
    {
        $uri = dirname(__FILE__) . '/../../../../samples/ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml';
        $doc = new XmlDocument('2.1');
        $doc->load($uri);
        
        $this->assertInstanceOf('qtism\\data\\storage\\xml\\XmlDocument', $doc);
        $this->assertInstanceOf('qtism\\data\\AssessmentTest', $doc->getDocumentComponent());
    }
    
    public function testLoadFileDoesNotExist()
    {
        // This file does not exist.
        $uri = dirname(__FILE__) . '/../../../../samples/invalid/abcd.xml';
        $doc = new XmlDocument('2.1');
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException');
        $doc->load($uri);
    }
    
    public function testLoadFileMalformed()
    {
        // This file contains malformed xml markup.
        $uri = dirname(__FILE__) . '/../../../../samples/invalid/malformed.xml';
        $doc = new XmlDocument('2.1');
        
        try {
            $doc->load($uri);
            $this->assertFalse(true); // An exception must have been thrown.
        } catch (XmlStorageException $e) {
            $this->assertInternalType('string', $e->getMessage());
            $this->assertInstanceOf('qtism\\data\\storage\\xml\\LibXmlErrorCollection', $e->getErrors());
            $this->assertGreaterThan(0, count($e->getErrors()));
        }
    }
    
    public function testLoadSimpleItemSessionControlOnTestPart()
    {
        $doc = new XmlDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/simple_itemsessioncontrol_testpart.xml');
        $testParts = $doc->getDocumentComponent()->getTestParts();
        $this->assertTrue($testParts['testPartId']->hasItemSessionControl());
        $this->assertInternalType('integer', 0, $testParts['testPartId']->getItemSessionControl()->getMaxAttempts());
        $this->assertEquals(0, $testParts['testPartId']->getItemSessionControl()->getMaxAttempts());
    }
    
    public function testSaveSimpleItemSessionControlOnTestPart()
    {
        $doc = new XmlDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/simple_itemsessioncontrol_testpart.xml');
        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        
        $doc = new XmlDocument('2.1');
        $doc->load($file);
        $testParts = $doc->getDocumentComponent()->getTestParts();
        $this->assertTrue($testParts['testPartId']->hasItemSessionControl());
        $this->assertInternalType('integer', 0, $testParts['testPartId']->getItemSessionControl()->getMaxAttempts());
        $this->assertEquals(0, $testParts['testPartId']->getItemSessionControl()->getMaxAttempts());
        
        unlink($file);
    }
    
    public function testFullyQualified()
    {
        $uri = dirname(__FILE__) . '/../../../../samples/custom/fully_qualified_assessmenttest.xml';
        $doc = new XmlDocument('2.1');
        $doc->load($uri);
        $doc->schemaValidate();
        
        $this->assertInstanceOf('qtism\\data\\storage\\xml\\XmlDocument', $doc);
        $this->assertInstanceOf('qtism\\data\\AssessmentTest', $doc->getDocumentComponent());
    }
    
    public function testItemSessionControls()
    {
        $doc = new XmlDocument('2.1');
        $doc->load(self::samplesDir() . 'custom/runtime/routeitem_itemsessioncontrols.xml');
        
        // Q01.
        $q01 = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $q01);
        $this->assertEquals(2, $q01->getItemSessionControl()->getMaxAttempts());
        
        // P02.
        $p02 = $doc->getDocumentComponent()->getComponentByIdentifier('P02');
        $this->assertInstanceOf('qtism\\data\\TestPart', $p02);
        $this->assertEquals(4, $p02->getItemSessionControl()->getMaxAttempts());
    }
    
    public function testAssessmentSectionRefsInTestParts()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/nested_assessment_section_refs/test_definition/test.xml', true);
        
        $testParts = $doc->getDocumentComponent()->getTestParts();
        $this->assertTrue(isset($testParts['T01']));
        
        $sectionParts = $testParts['T01']->getAssessmentSections();
        $this->assertTrue(isset($sectionParts['SR01']));
        $this->assertInstanceOf('qtism\\data\\AssessmentSectionRef', $sectionParts['SR01']);
    }

    /**
     * @dataProvider includeAssessmentSectionRefsInTestPartsProvider
     */
    public function testIncludeAssessmentSectionRefsInTestParts($file, $filesystem)
    {
        $doc = new XmlDocument();

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $doc->load($file, true);
        $doc->includeAssessmentSectionRefs();
        
        $root = $doc->getDocumentComponent();
        
        $testParts = $root->getTestParts();
        $this->assertTrue(isset($testParts['T01']));
        
        // Check that assessmentSectionRef 'SR01' has been resolved.
        $sectionParts = $testParts['T01']->getAssessmentSections();
        
        $this->assertTrue(isset($sectionParts['S01']));
        $this->assertFalse(isset($sectionParts['SR01']));
        $this->assertTrue(isset($sectionParts['S01']->getSectionParts()['S02']));
        
        // Check that the final assessmentSection contains the assessmentItemRefs.
        $assessmentItemRefs = $sectionParts['S01']->getSectionParts()['S02']->getSectionParts();
        $this->assertEquals(3, count($assessmentItemRefs));
        
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $assessmentItemRefs['Q01']);
        $this->assertEquals('../sections/../sections/../items/question1.xml', $assessmentItemRefs['Q01']->getHref());
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $assessmentItemRefs['Q02']);
        $this->assertEquals('../sections/../sections/../items/question2.xml', $assessmentItemRefs['Q02']->getHref());
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $assessmentItemRefs['Q03']);
        $this->assertEquals('../sections/../sections/../items/question3.xml', $assessmentItemRefs['Q03']->getHref());
    }

    public function includeAssessmentSectionRefsInTestPartsProvider()
    {
        return [
            [self::samplesDir() . 'custom/tests/nested_assessment_section_refs/test_definition/test.xml', false],
            ['custom/tests/nested_assessment_section_refs/test_definition/test.xml', true]
        ];
    }
    
    /**
     * @dataProvider testIncludeAssessmentSectionRefsMixedProvider
     */
    public function testIncludeAssessmentSectionRefsMixed($file, $filesystem)
    {
        $doc = new XmlDocument();

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $doc->load($file, true);
        $doc->includeAssessmentSectionRefs(true);

        $root = $doc->getDocumentComponent();

        $testParts = $root->getTestParts();
        $this->assertTrue(isset($testParts['T01']));

        $this->assertCount(1, $testParts['T01']->getAssessmentSections());
        $this->assertTrue(isset($testParts['T01']->getAssessmentSections()['S00']));

        $mainSection = $testParts['T01']->getAssessmentSections()['S00'];
        $sectionParts = $mainSection->getSectionParts();
        $this->assertCount(5, $sectionParts);
        $this->assertSame(
            array('Q01', 'S01', 'Q03', 'S02', 'Q05'),
            $sectionParts->getKeys()
        );

        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $sectionParts['Q01']);
        $this->assertInstanceOf('qtism\\data\\AssessmentSection', $sectionParts['S01']);
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $sectionParts['Q03']);
        $this->assertInstanceOf('qtism\\data\\AssessmentSection', $sectionParts['S02']);
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $sectionParts['Q05']);

        $section = $sectionParts['S01'];
        $this->assertCount(1, $section->getSectionParts());
        $this->assertTrue(isset($section->getSectionParts()['Q02']));
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $section->getSectionParts()['Q02']);

        $section = $sectionParts['S02'];
        $this->assertCount(1, $section->getSectionParts());
        $this->assertTrue(isset($section->getSectionParts()['Q04']));
        $this->assertInstanceOf('qtism\\data\\AssessmentItemRef', $section->getSectionParts()['Q04']);
    }

    public function testIncludeAssessmentSectionRefsMixedProvider()
    {
        return array(
            array(self::samplesDir() . 'custom/tests/mixed_assessment_section_refs/test_similar_ids.xml', false),
            array(self::samplesDir() . 'custom/tests/mixed_assessment_section_refs/test_different_ids.xml', false),
            array('custom/tests/mixed_assessment_section_refs/test_similar_ids.xml', true),
            array('custom/tests/mixed_assessment_section_refs/test_different_ids.xml', true)
        );
    }
    
    private static function decorateUri($uri)
    {
        return dirname(__FILE__) . '/../../../../samples/ims/tests/' . $uri;
    }
}
