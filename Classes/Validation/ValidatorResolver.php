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
 * @subpackage Validation
 *
 */
class Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver extends Tx_Extbase_Validation_ValidatorResolver {

	/**
	 * @var Tx_LwEnetMultipleActionForms_Service_ActionSequence
	 */
	protected $actionSequenceService;

	/**
	 * Resolves and returns the base validator conjunction for the given data type.
	 *
	 * If no validator could be resolved (which usually means that no validation is necessary),
	 * NULL is returned.
	 *
	 * @param string $dataType The data type to search a validator for. Usually the fully qualified object name
	 * @return Tx_Extbase_Validation_Validator_ConjunctionValidator The validator conjunction or NULL
	 */
	public function getBaseValidatorConjunction($dataType) {
		$this->actionSequenceService = t3lib_div::makeInstance(
			'Tx_LwEnetMultipleActionForms_Service_ActionSequence'
		);
		$isActionSequenceController = FALSE;
		if ($this->actionSequenceService->getRequest() instanceof Tx_Extbase_MVC_RequestInterface) {
			$isActionSequenceController = in_array(
				'Tx_LwEnetMultipleActionForms_MVC_Controller_AbstractController',
				class_parents(
					$this->actionSequenceService->getRequest()->getControllerObjectName()
				)
			);
		}
		if ($isActionSequenceController === TRUE) {
			$this->baseValidatorConjunctions[$dataType] = $this->buildBaseValidatorConjunction($dataType);
		} else {
			if (!isset($this->baseValidatorConjunctions[$dataType])) {
				$this->baseValidatorConjunctions[$dataType] = parent::buildBaseValidatorConjunction($dataType);
			}
		}
		return $this->baseValidatorConjunctions[$dataType];
	}

	/**
	 * Builds a base validator conjunction for the given data type.
	 *
	 * The base validation rules are those which were declared directly in a class (typically
	 * a model) through some @validate annotations on properties.
	 *
	 * Additionally, if a custom validator was defined for the class in question, it will be added
	 * to the end of the conjunction. A custom validator is found if it follows the naming convention
	 * "Replace '\Model\' by '\Validator\' and append "Validator".
	 *
	 * Example: $dataType is F3\Foo\Domain\Model\Quux, then the Validator will be found if it has the
	 * name F3\Foo\Domain\Validator\QuuxValidator
	 *
	 * @param string $dataType The data type to build the validation conjunction for. Needs to be the fully qualified object name.
	 * @return Tx_Extbase_Validation_Validator_ConjunctionValidator The validator conjunction or NULL
	 */
	protected function buildBaseValidatorConjunction($dataType) {

		/** @var $validatorConjunction Tx_Extbase_Validation_Validator_ConjunctionValidator */
		$validatorConjunction = $this->objectManager->get('Tx_Extbase_Validation_Validator_ConjunctionValidator');

		// Model based validator
		if (strstr($dataType, '_') !== FALSE && class_exists($dataType)) {
			$validatorCount = 0;

			/** @var $objectValidator Tx_LwEnetMultipleActionForms_Validation_Validator_GenericObjectValidator */
			$objectValidator = $this->createValidator('GenericObject');

			foreach ($this->reflectionService->getClassPropertyNames($dataType) as $propertyName) {
				$classPropertyTagsValues = $this->reflectionService->getPropertyTagsValues($dataType, $propertyName);

				if (isset($classPropertyTagsValues['validateControllerConstraint'])) {
					$isPropertyValidationRequired = $this->actionSequenceService->isPropertyValidationRequired(
						$classPropertyTagsValues['validateControllerConstraint']
					);
					if ($isPropertyValidationRequired === FALSE) {
						continue;
					}
				}

				/** @var $nestedObjectValidatorResolver Tx_LwEnetMultipleActionForms_Validation_NestedObjectValidatorResolver */
				$nestedObjectValidatorResolver = $this->objectManager->get('Tx_LwEnetMultipleActionForms_Validation_NestedObjectValidatorResolver');
				$nestedObjectValidatorResolver->buildNestedObjectsBaseValidators($dataType, $objectValidator, $propertyName);

					// Add @validate annotation property validators
				$this->addPropertyValidators($objectValidator, $propertyName, $classPropertyTagsValues);
			}
			/**
			 * Removed validator count check to add property related validators
			 * in GenericObjectValidator -> @validatePropertyConstraint
			 */
			$validatorConjunction->addValidator($objectValidator);
		}

		// Custom validator for the class
		$possibleValidatorClassName = str_replace('_Model_', '_Validator_', $dataType) . 'Validator';
		$customValidator = $this->createValidator($possibleValidatorClassName);
		if ($customValidator !== NULL) {
			$validatorConjunction->addValidator($customValidator);
		}

		return $validatorConjunction;
	}

	/**
	 * Adds all property validators from the property information stack to the given generic validator
	 *
	 * @param Tx_Extbase_Validation_Validator_GenericObjectValidator $genericValidator The generic validator target
	 * @param string $propertyName The name of the property
	 * @param array $propertyInformation The property informations
	 * @return int
	 */
	protected function addPropertyValidators($genericValidator, $propertyName, $propertyInformation) {
		if(!isset($propertyInformation['validate'])) {
			return;
		}
		$validatorCount = 0;
		foreach($propertyInformation['validate'] as $validateValue) {
			$parsedAnnotation = $this->parseValidatorAnnotation($validateValue);
			foreach($parsedAnnotation['validators'] as $validatorConfiguration) {
				$validator = $this->createValidator($validatorConfiguration['validatorName'], $validatorConfiguration['validatorOptions']);
				if($validator === null) {
					continue;
				}
				$genericValidator->addPropertyValidator($propertyName, $validator);
				$validatorCount++;
			}
		}
		return $validatorCount;
	}

	/**
	* Returns an object of an appropriate validator for the given class. In $possibleClassNames
	* is the class name defined with the Namespace of Tx_LwEnetMultipleActionForms and
	* of the default Tx_Extbase. If no validator is available FALSE is returned
	*
	* @param string $validatorName Either the fully qualified class name of the validator or the short name of a built-in validator
	* @return string Name of the validator object or FALSE
	*/
	protected function resolveValidatorObjectName($validatorName) {
		if (strstr($validatorName, '_') !== FALSE && class_exists($validatorName)) return $validatorName;

		$possibleClassNames = array(
			'Tx_LwEnetMultipleActionForms_Validation_Validator_' . $this->unifyDataType($validatorName) . 'Validator',
			'Tx_Extbase_Validation_Validator_' . $this->unifyDataType($validatorName) . 'Validator'
		);

		foreach ($possibleClassNames as $possibleClassName) {
			if (class_exists($possibleClassName)) {
				return $possibleClassName;
			}
		}

		return FALSE;
	}
}

?>