<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Validator resolver to automatically find a appropriate validator for a given subject
 *
 * @package Extbase
 * @subpackage Validation
 * @version $Id$
 */
class Tx_LwEnetMultipleActionForms_MVC_Controller_Action_Override_ConstraintResolver extends Tx_LwEnetMultipleActionForms_Comparison_ConstraintResolver {

	/**
	 * @param string $className
	 * @param string $methodName
	 * @return array
	 * @throws Tx_LwEnetMultipleActionForms_MVC_Controller_Action_Override_Exception_InvalidConditionConfiguration|Tx_LwEnetMultipleActionForms_MVC_Controller_Action_Override_Exception_NoSuchCondition
	 */
	public function buildMethodArgumentsOverrideActionConstraints($className, $methodName) {
		$constraintConjunctions = array();

		$methodParameters = $this->reflectionService->getMethodParameters($className, $methodName);
		$methodTagsValues = $this->reflectionService->getMethodTagsValues($className, $methodName);

		if (!count($methodParameters)) {
			// early return in case no parameters were found.
			return $constraintConjunctions;
		}

		if (isset($methodTagsValues['actionOverrideConstraint'])) {
			foreach ($methodParameters as $parameterName => $methodParameter) {
				$constraintConjunctions[$parameterName] = $this->createConstraint('Conjunction');
			}

			$parsedConstraints = Tx_LwEnetMultipleActionForms_Utility_Annotations_ConstraintParser::parseConstraintAnnotations(
				$methodTagsValues['actionOverrideConstraint']
			);
			foreach ($parsedConstraints as $parsedConstraint) {
				if (is_array($parsedConstraint['constraintOptions'])) {
					$newConstraint = $this->createConstraint(
						$parsedConstraint['constraintName'],
						$parsedConstraint['constraintOptions']
					);
				} else {
					$newConstraint = $this->createConstraint($parsedConstraint['constraintName']);
				}
				if ($newConstraint === NULL) {
					throw new Tx_LwEnetMultipleActionForms_Comparison_Exception_NoSuchConstraint(
						'Invalid overrideCondition annotation in ' . $className . '->' . $methodName . '(): Could not resolve class name for overrideCondition "' . $parsedConstraint['constraintName'] . '".',
						1322225440
					);
				}

				if  (isset($constraintConjunctions[$parsedConstraint['constraintProperty']])) {
					$constraintConjunctions[$parsedConstraint['constraintProperty']]->addConstraint(
						$newConstraint
					);
				} else {
					throw new Tx_LwEnetMultipleActionForms_Comparison_Exception_InvalidConstraintConfiguration(
						'Invalid overrideCondition annotation in ' . $className . '->' . $methodName . '(): OverrideCondition specified for argument name "' . $parsedConstraint['constraintProperty'] . '", but this argument does not exist.',
						1253172726
					);
				}
			}
		}
		return $constraintConjunctions;
	}
}

?>