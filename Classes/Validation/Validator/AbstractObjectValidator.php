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
abstract class Tx_LwEnetMultipleActionForms_Validation_Validator_AbstractObjectValidator extends Tx_Extbase_Validation_Validator_GenericObjectValidator {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_LwEnetMultipleActionForms_Service_ActionSequence
	 */
	protected $actionSequenceService;

	/**
	 * @var object
	 */
	protected $object;

	/**
	 * @var Tx_LwEnetMultipleActionForms_Comparison_ConstraintResolver
	 */
	protected $constraintResolver;

	/**
	 * @var Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * Injects the object manager
	 *
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_LwEnetMultipleActionForms_Comparison_ConstraintResolver $constraintResolver
	 */
	public function injectConstraintResolver(Tx_LwEnetMultipleActionForms_Comparison_ConstraintResolver $constraintResolver) {
		$this->constraintResolver = $constraintResolver;
	}

	/**
	 * @param Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver $validatorResolver
	 */
	public function injectValidatorResolver(Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

	/**
	 * Injects the reflection service
	 *
	 * @param Tx_Extbase_Reflection_Service $reflectionService
	 * @return void
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @throws Exception
	 */
	protected function initializeActionSequenceService() {
		/** @var $actionFlowService Tx_LwEnetMultipleActionForms_Service_ActionSequence */
		$this->actionSequenceService = t3lib_div::makeInstance(
			'Tx_LwEnetMultipleActionForms_Service_ActionSequence'
		);
	}

	/**
	 * @param string $propertyName
	 * @param array $constraints
	 */
	protected function buildValidatePropertyConstraintValidators($propertyName, array $constraints) {
			/** @var $objectAccess Tx_Extbase_Reflection_ObjectAccess */
		$objectAccess = $this->objectManager->get('Tx_Extbase_Reflection_ObjectAccess');

		$parsedConstraints = Tx_LwEnetMultipleActionForms_Utility_Annotations_ConstraintParser::parseConstraintAnnotations($constraints);
		foreach ($parsedConstraints as $parsedConstraint) {
			if ($objectAccess->isPropertyGettable($this->object, $parsedConstraint['constraintProperty']) === FALSE) {
				throw new Exception(
					'validatePropertyConstraint defined for non accessible property: ' . $parsedConstraint['constraintProperty'],
					1330431613
				);
			}

			$constraintPropertyValue = call_user_func(
				array($this->object, 'get' . ucfirst($parsedConstraint['constraintProperty']))
			);

				// @todo: add boolean logic to solve complex cases like:
				// a property must validate if:
				//		prop a is foo AND prop b is bar
				//			OR
				//		prop c is foobar AND prop b is bar
				// We have this case with serialnumber, that must only validate if
				// type is not "customer care" and context is public,
				// OR
				// type is not "customer care" and conetx ist authenticated
				// for now we solved this by adding the otherwise mising serialnumber
				// property as hidden field
			$constraint = $this->createConstraint($parsedConstraint);
			$constraintIsComplied = $constraint->isComplied($constraintPropertyValue);

			if ($constraintIsComplied === TRUE && is_array($parsedConstraint['constraintValidators'])) {
				foreach ($parsedConstraint['constraintValidators'] as $validatorConfiguration) {
					$validator = $this->createValidator($validatorConfiguration);
					$this->addPropertyValidator($propertyName, $validator);
				}
			}
		}
	}

	/**
	 * @param array $constraintConfiguration
	 * @return Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface
	 */
	protected function createConstraint(array $constraintConfiguration) {
		if (is_array($constraintConfiguration['constraintOptions'])) {
			/** @var $constraint Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface */
			$constraint = $this->constraintResolver->createConstraint(
				$constraintConfiguration['constraintName'],
				$constraintConfiguration['constraintOptions']
			);
		} else {
			/** @var $constraint Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface */
			$constraint = $this->constraintResolver->createConstraint(
				$constraintConfiguration['constraintName']
			);
		}
		if (is_null($constraint)) {
			throw new Tx_LwEnetMultipleActionForms_Comparison_Exception_NoSuchConstraint(
				'Failed to create constraint: ' . $constraintConfiguration['constraintName'],
				1323094815
			);
		}
		return $constraint;
	}

	/**
	 * @param array $validatorConfiguration
	 * @return Tx_Extbase_Validation_Validator_ValidatorInterface
	 */
	protected function createValidator(array $validatorConfiguration) {
		if (is_array($validatorConfiguration['validatorOptions'])) {
			/** @var $validator Tx_Extbase_Validation_Validator_ValidatorInterface */
			$validator = $this->validatorResolver->createValidator(
				$validatorConfiguration['validatorName'],
				$validatorConfiguration['validatorOptions']
			);
		} else {
			/** @var $validator Tx_Extbase_Validation_Validator_ValidatorInterface */
			$validator = $this->validatorResolver->createValidator(
				$validatorConfiguration['validatorName']
			);
		}
		if (is_null($validator)) {
			throw new Tx_Extbase_Validation_Exception_NoSuchValidator(
				'Failed to create validator: ' . $validatorConfiguration['validatorName'],
				1323085308
			);
		}
		return $validator;
	}

	/**
	 * @return int
	 */
	public function getPropertyValidatorCount() {
		return count($this->propertyValidators);
	}
}

?>