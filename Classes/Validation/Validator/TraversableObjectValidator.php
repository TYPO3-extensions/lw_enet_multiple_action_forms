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
class Tx_LwEnetMultipleActionForms_Validation_Validator_TraversableObjectValidator extends Tx_LwEnetMultipleActionForms_Validation_Validator_AbstractObjectValidator {

	/**
	* @var Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver
	*/
	protected $validatorResolver;

	/**
	* property name
	*
	* @var string
	*/
	protected $propertyName;

	/**
	* Injects the validator resolver
	*
	* @param Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver $validatorResolver
	* @return void
	*/
	public function injectValidatorResolver(Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

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
		if (!is_object($value)) {
			$this->addError('Value is no object.', 1337075085);
			return FALSE;
		}
		$count = 0;

			// check if $value is a traversable object and valdidate the subobjects
		if ($this->isObjectTraversable($value)) {
			foreach ($value->toArray() as $index => $element) {
				$index++;
				$elementClassName = get_class($element);

				/** @var $validator Tx_Extbase_Validation_Validator_ConjunctionValidator */
				$validator = $this->validatorResolver->getBaseValidatorConjunction($elementClassName);

				if ($validator->isValid($element) === FALSE) {
					$errors = $validator->getErrors();
					$this->addErrorsForProperty($errors, $this->propertyName . '-' . $index);
					$count++;
				}
			}
		}

		$result = FALSE;
		if ($count === 0) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	* @param array $errors Array of Tx_Extbase_Validation_Error
	* @param string $propertyName Name of the property to add errors
	* @return void
	*/
	protected function addErrorsForProperty($errors, $propertyName) {
		if (!isset($this->errors[$propertyName])) {
			$this->errors[$propertyName] = new Tx_Extbase_Validation_PropertyError($propertyName);
		}
		$this->errors[$propertyName]->addErrors($errors);
	}

	/**
	 * Method checks if the object that has to be validated is traversable. If the propertyName
	 * and the dataType of the TraversableObjectValidator is not set the method returns FALSE.
	 * Without propertyName and dataType it is not possible to get the property informations.
	 *
	 * If the value of $propertyInformation['type'] is an array, ArrayObject, SplObjectStorage
	 * or a Tx_Extbase_Persistence_ObjectStorage the method returns TRUE, otherwise FALSE.
	 *
	 * @param mixed $object
	 * @return boolean
	 */
	protected function isObjectTraversable($object) {
		$result = FALSE;

			// array with the implemented classes or FALSE
		$implementedClasses = class_implements($object);
		if (is_array($implementedClasses)) {
			foreach ($implementedClasses as $implementedClass) {
				if (is_array($object) || in_array($implementedClass, array('ArrayObject', 'SplObjectStorage', 'Tx_Extbase_Persistence_ObjectStorage', 'Traversable'))) {
					$result = TRUE;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Get propertyName
	 *
	 * @return $propertyName
	 */
	public function getPropertyName() {
		return $this->propertyName;
	}

	/**
	 * Set propertyName
	 *
	 * @param string $propertyName
	 */
	public function setPropertyName($propertyName) {
		$this->propertyName = $propertyName;
	}
}
?>