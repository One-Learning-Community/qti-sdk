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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */


namespace qtism\data\rules;

/**
 * A QTISM specific class representing a Rule in QTI. This class was created
 * in order to abstract ResponseRule and OutcomeRule QTI classes that are actually
 * exactly the same.
 * 
 * Note: This interface acts as a marker interface.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @link http://en.wikipedia.org/wiki/Marker_interface_pattern
 *
 */
interface Rule {
	
}
