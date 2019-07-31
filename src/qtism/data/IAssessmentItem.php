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

namespace qtism\data;

use qtism\common\collections\IdentifierCollection;
use qtism\data\processing\TemplateProcessing;
use qtism\data\content\ModalFeedbackRuleCollection;
use qtism\data\state\TemplateDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\processing\ResponseProcessing;
use \InvalidArgumentException;

/**
 * Any clas that claims to represent An AssessmentItem must implement this interface.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface IAssessmentItem extends QtiIdentifiable
{
    /**
     * Set whether the item is time dependent or not.
     *
     * @param boolean $timeDependent Time dependent or not.
     * @throws \InvalidArgumentException If $timeDependent is not a boolean value.
     */
    public function setTimeDependent($timeDependent);

    /**
     * Wether the item is time dependent.
     *
     * @return boolean
     */
    public function isTimeDependent();

    /**
     * Set whether the item is adaptive.
     *
     * @param boolean $adaptive Adaptive or not.
     * @throws \InvalidArgumentException If $adaptive is not a boolean value.
     */
    public function setAdaptive($adaptive);

    /**
     * Whether the item is adaptive.
     *
     * @return boolean
     */
    public function isAdaptive();

    /**
     * Get the response declarations.
     *
     * @return \qtism\data\state\ResponseDeclarationCollection A collection of ResponseDeclaration objects.
     */
    public function getResponseDeclarations();

    /**
     * Set the response declarations.
     *
     * @param \qtism\data\state\ResponseDeclarationCollection $responseDeclarations A collection of ResponseDeclaration objects
     */
    public function setResponseDeclarations(ResponseDeclarationCollection $responseDeclarations);

    /**
     * Get the outcome declarations.
     *
     * @return \qtism\data\state\OutcomeDeclarationCollection A collection of OutcomeDeclaration objects.
     */
    public function getOutcomeDeclarations();

    /**
     * Set the outcome declarations.
     *
     * @param \qtism\data\state\OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations);
    
    /**
     * Get the template declarations.
     * 
     * @return \qtism\data\state\TemplateDeclarationCollection $templateDeclarations
     */
    public function getTemplateDeclarations();
    
    /**
     * Set the template declarations.
     * 
     * @param \qtism\data\state\TemplateDeclarationCollection $templateDeclarations
     */
    public function setTemplateDeclarations(TemplateDeclarationCollection $templateDeclarations);
    
    /**
     * Get the modal feedback rules.
     * 
     * @return \qtism\data\content\ModalFeedbackRuleCollection
     */
    public function getModalFeedbackRules();

    /**
     * Get the associated ResponseProcessing object.
     *
     * @return \qtism\data\processing\ResponseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function getResponseProcessing();

    /**
     * Set the associated ResponseProcessing object.
     *
     * @param \qtism\data\processing\ResponseProcessing $responseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function setResponseProcessing(ResponseProcessing $responseProcessing = null);
    
    /**
     * Get the associated TemplateProcessing object.
     *
     * @return \qtism\data\processing\TemplateProcessing A TemplateProcessing object or null if no associated template processing.
     */
    public function getTemplateProcessing();
    
    /**
     * Set the associated TemplateProcessing object.
     *
     * @param \qtism\data\processing\TemplateProcessing $templateProcessing A TemplateProcessing object or null if no associated template processing.
    */
    public function setTemplateProcessing(TemplateProcessing $templateProcessing = null);
    
    /**
     * Get the response variable identifiers related to endAttemptInteractions in the item content.
     * 
     * @return \qtism\common\collections\IdentifierCollection
     */
    public function getEndAttemptIdentifiers();
    
    /**
     * Get the ShufflingCollection object representing how choices are shuffled in shuffled interactions.
     * 
     * @return \qtism\data\state\ShufflingCollection
     */
    public function getShufflings();
    
    /**
     * Get the ResponseValidityConstraintCollection object.
     * 
     * The ResponseValidityConstraint objects returned describes how the responses provided to make
     * an attempt on the item should be validated.
     * 
     * @return \qtism\data\state\ResponseValidityConstraintCollection
     */
    public function getResponseValidityConstraints();


    /**
     * Set the title.
     *
     * Set the title of the assessmentItem.
     *
     * @param string $title
     * @throws \InvalidArgumentException
     */
    public function setTitle($title);

    /**
     * Get the title.
     *
     * Get the title of the assessmentItem.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set the label.
     *
     * Set the label of the assessmentItem
     *
     * @param $label
     * @throws \InvalidArgumentException
     */
    public function setLabel($label);

    /**
     * Get the label.
     *
     * Get the label of the assessmentItem.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Has a label.
     *
     * Whether or not the assessmentItem has a label.
     *
     * @return string
     */
    public function hasLabel();
}
