<?php

namespace qtism\runtime\expressions\processing\operators;

use qtism\data\expressions\operators\PatternMatch;
use qtism\data\expressions\Expression;
use qtism\runtime\expressions\processing\operators\Utils as OperatorUtils;
use \InvalidArgumentException;

/**
 * The PatternProcessor class aims at processing Pattern expressions.
 * 
 * From IMS QTI:
 * 
 * The patternMatch operator takes a sub-expression which must have single cardinality and 
 * a base-type of string. The result is a single boolean with a value of true if the 
 * sub-expression matches the regular expression given by pattern and false if it doesn't.
 * If the sub-expression is NULL then the operator results in NULL.
 * 
 * The syntax for the regular expression language is as defined in Appendix F of [XML_SCHEMA2].
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @link http://www.w3.org/TR/xmlschema-2/#regexs
 *
 */
class PatternMatchProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof PatternMatch) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The PatternMatchProcessor class only processes PatternMatch QTI Data Model objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the PatternMatch.
	 * 
	 * @return boolean|null A single boolean with a value of true if the sub-expression matches the pattern and false if it does not. If the sub-expression is NULL, the the operator results in NULL.
	 * @throws OperatorProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if ($operands->containsNull() === true) {
			return null;
		}
		
		if ($operands->exclusivelySingle() === false) {
			$msg = "The PatternMatch operator only accepts operands with a single cardinality.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		if ($operands->exclusivelyString() === false) {
			$msg = "The PatternMatch operator only accepts operands with a string baseType.";
			throw new OperatorProcessingException($msg, $this);
		}
		
		// XML schema always implicitly anchors the entire regular expression
		// because there is no carret (^) nor dollar ($) signs.
		// see http://www.regular-expressions.info/xml.html
		$rawPattern = $this->getExpression()->getPattern();
		$pattern = OperatorUtils::escapeSymbols($rawPattern, array('$', '^'));
		$pattern = OperatorUtils::pregAddDelimiter('^' . $pattern . '$');
		
		// XSD regexp always case-sensitive (nothing to do), dot matches white-spaces (use PCRE_DOTALL).
		$pattern .= 's';
		
		$result = @preg_match($pattern, $operands[0]);
		
		if ($result === 1) {
			return true;
		}
		else if ($result === 0) {
			return false;
		}
		else {
			$error = preg_last_error();
			$errorType = 'PCRE Engine compilation error';
			
			switch ($error) {
				case PREG_INTERNAL_ERROR:
					$errorType = "PCRE Engine internal error";
				break;
				
				case PREG_BACKTRACK_LIMIT_ERROR:
					$errorType = "PCRE Engine backtrack limit exceeded";
				break;
				
				case PREG_RECURSION_LIMIT_ERROR:
					$errorType = "PCRE Engine recursion limit exceeded";
				break;
				
				case PREG_BAD_UTF8_ERROR:
					$errorType = "PCRE Engine malformed UTF-8";
				break;
				
				case PREG_BAD_UTF8_OFFSET_ERROR:
					$errorType = "PCRE Engine UTF-8 offset error";
				break;
			}
			
			$msg = "An internal error occured while processing the regular expression '${rawPattern}': ${errorType}.";
			throw new OperatorProcessingException($msg, $this, OperatorProcessingException::RUNTIME_ERROR);
		}
	}
}