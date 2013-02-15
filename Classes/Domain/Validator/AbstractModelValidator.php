<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Christian Kuhn <lolli@schwarzbu.ch>
*  All rights reserved
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
 * Form validator
 *
 * @package TYPO3
 * @subpackage lw_enet_multiple_action_forms
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class Tx_LwEnetMultipleActionForms_Domain_Validator_AbstractModelValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

	/**
	 * @var Tx_LwEnetMultipleActionForms_Service_ActionSequence
	 */
	protected $actionSequenceService;

	/**
	 * Injects the action sequence service
	 *
	 * @param Tx_LwEnetMultipleActionForms_Service_ActionSequence $actionSequenceService
	 * @return void
	 */
	public function injectActionSequenceService(Tx_LwEnetMultipleActionForms_Service_ActionSequence $actionSequenceService) {
		$this->actionSequenceService = $actionSequenceService;
	}

	/**
	 * Is valid
	 *
	 * @param Tx_Extbase_DomainObject_AbstractDomainObject $object
	 * @return boolean $isValid
	 */
	public function isValid($object) {
			/** @var $object Tx_Extbase_DomainObject_AbstractDomainObject */
		if (!($object instanceof Tx_Extbase_DomainObject_AbstractDomainObject)) {
			throw new Exception(
				'Value is not an instance of Tx_Extbase_DomainObject_AbstractDomainObject',
				1327485943
			);
		}

		$isValid = TRUE;
		try {
			$this->callValidationMethods($object);
		} catch (Tx_Extbase_Validation_Exception $e) {
			$isValid = FALSE;
		}

		if (count($this->errors) > 0) {
			$isValid = FALSE;
		}

		return $isValid;
	}

	/**
	 * Call validation methods
	 *
	 * @param Tx_Extbase_DomainObject_AbstractDomainObject $object
	 * @return void
	 */
	protected function callValidationMethods(Tx_Extbase_DomainObject_AbstractDomainObject $object) {
			// Call generic method for all action if exists
		if (method_exists($this, 'generalValidation')) {
			$this->generalValidation($object);
		}

			// Call specifec method per action if exists
		$actionValidationMethodName = $this->actionSequenceService->getCurrentAction()->getActionMethodName() . 'Validation';
		if (method_exists($this, $actionValidationMethodName)) {
			call_user_func(
				array($this, $actionValidationMethodName),
				$object
			);
		}
	}

	/**
	 * Add a property error to error container.
	 * This is done by extbase itself for single validators at properties, but
	 * not in our 'validate the whole model' context here. The methods mimics
	 * the property error adding handling and must be called explicitly.
	 *
	 * @param string $propertyName
	 * @param string $message
	 * @param string $code
	 */
	protected function addPropertyError($propertyName, $message, $code) {
		if (!($this->errors[$propertyName] instanceof Tx_Extbase_Validation_PropertyError)) {
			$propertyError = new Tx_Extbase_Validation_PropertyError($propertyName);
		} else {
			$propertyError = $this->errors[$propertyName];
		}
		$propertyError->addErrors(
			array(
				new Tx_Extbase_Validation_Error($message, $code)
			)
		);
		$this->errors[$propertyName] = $propertyError;
	}

	/**
	 * @param $propertyName
	 * @param $childPropertyName
	 * @param Tx_Extbase_Validation_PropertyError $childPropertyError
	 */
	protected function addChildPropertyErrorToProperty($propertyName, $childPropertyName, Tx_Extbase_Validation_PropertyError $childPropertyError) {
		if (!($this->errors[$propertyName] instanceof Tx_Extbase_Validation_PropertyError)) {
			$propertyError = new Tx_Extbase_Validation_PropertyError($propertyName);
		} else {
			$propertyError = $this->errors[$propertyName];
		}
		$propertyError->addErrors(
			array(
				$childPropertyName => $childPropertyError
			)
		);
		$this->errors[$propertyName] = $propertyError;
	}

	/**
	 * @param string $propertyName
	 * @param string $message
	 * @param string $code
	 * @return Tx_Extbase_Validation_PropertyError
	 */
	protected function createPropertyError($propertyName, $message, $code) {
		$propertyError = new Tx_Extbase_Validation_PropertyError($propertyName);
		$propertyError->addErrors(
			array(
				new Tx_Extbase_Validation_Error($message, $code)
			)
		);
		return $propertyError;
	}
}

?>