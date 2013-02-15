<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Helge Funk <helge.funk@e-net.info>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 *
 * @package LwEnetMultipleActionForms
 * @subpackage Validation|Validator
 *
 */
class Tx_LwEnetMultipleActionForms_Validation_Validator_GenericObjectValidator extends Tx_LwEnetMultipleActionForms_Validation_Validator_AbstractObjectValidator {

	/**
	 * Checks if the given value is valid according to the property validators
	 *
	 * If at least one error occurred, the result is FALSE.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 * @api
	 */
	public function isValid($value) {
		if (is_null($value)) {
			return TRUE;
		}
		if (!is_object($value)) {
			$this->addError('Value is no object.', 1324568051);
			return FALSE;
		}
		$this->object = $value;
		$objectClassName = get_class($this->object);
		$this->initializeActionSequenceService();

		foreach ($this->reflectionService->getClassPropertyNames($objectClassName) as $propertyName) {
			$propertyTagsValues = $this->reflectionService->getPropertyTagsValues(
				get_class($this->object),
				$propertyName
			);

			$isPropertyValidationRequired = TRUE;
			if (is_array($propertyTagsValues['validateControllerConstraint'])) {
				$isPropertyValidationRequired = $this->actionSequenceService->isPropertyValidationRequired(
					$propertyTagsValues['validateControllerConstraint']
				);
			}

			if (is_array($propertyTagsValues['validatePropertyConstraint']) &&  $isPropertyValidationRequired === TRUE) {
				$this->buildValidatePropertyConstraintValidators(
					$propertyName,
					$propertyTagsValues['validatePropertyConstraint']
				);
			}
		}

		$result = TRUE;
		foreach (array_keys($this->propertyValidators) as $propertyName) {
			if ($this->isPropertyValid($this->object, $propertyName) === FALSE) {
				$result = FALSE;
			}
		}
		return $result;
	}

}

?>