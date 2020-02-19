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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\common\datatypes\QtiShape;
use qtism\data\expressions\operators\Inside;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\storage\Utils;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of inside QTI operators.
 */
class InsideMarshaller extends OperatorMarshaller
{
    /**
     * @see \qtism\data\storage\xml\marshalling\OperatorMarshaller::marshallChildrenKnown()
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        $this->setDOMElementAttribute($element, 'shape', QtiShape::getNameByConstant($component->getShape()));
        $this->setDOMElementAttribute($element, 'coords', $component->getCoords());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\OperatorMarshaller::unmarshallChildrenKnown()
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        if (($shape = $this->getDOMElementAttributeAs($element, 'shape')) !== null) {
            if (($coords = $this->getDOMElementAttributeAs($element, 'coords')) !== null) {
                $shape = QtiShape::getConstantByName($shape);
                $coords = Utils::stringToCoords($coords, $shape);

                $object = new Inside($children, $shape, $coords);

                return $object;
            } else {
                $msg = "The mandatory attribute 'coords' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'shape' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
