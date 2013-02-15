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
class Tx_LwEnetMultipleActionForms_Validation_NestedObjectValidatorResolver extends Tx_Extbase_Validation_ValidatorResolver {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @var Tx_Extbase_Validation_ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * Injects the object manager
	 *
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager A reference to the object manager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
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
	 * Injects the validator resolver of Tx_LwEnetMultipleActionForms
	 *
	 * @param Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver $validatorResolver
	 * @return void
	 */
	public function injectValidatorResolver(Tx_LwEnetMultipleActionForms_Validation_ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

	/**
	 * @param string $dataType
	 * @param Tx_LwEnetMultipleActionForms_Validation_Validator_GenericObjectValidator $genericValidator
	 * @param string $propertyName
	 * @return Tx_Extbase_Validation_Validator_ValidatorInterface
	 */
	public function buildNestedObjectsBaseValidators($dataType, Tx_LwEnetMultipleActionForms_Validation_Validator_GenericObjectValidator $genericValidator, $propertyName) {
			// Early return if dataType is not an object
		/** @var $classSchema Tx_Extbase_Reflection_ClassSchema */
		$classSchema = $this->reflectionService->getClassSchema($dataType);
		if (is_null($classSchema)) {
			return;
		}

		$propertyInformation = array_merge(
			$this->reflectionService->getPropertyTagsValues($dataType, $propertyName),
			$classSchema->getProperty($propertyName)
		);

		$isTraversableObjectType = in_array(
			$propertyInformation['type'],
			array('array', 'ArrayObject', 'SplObjectStorage', 'Tx_Extbase_Persistence_ObjectStorage')
		);

			// @todo: verify if condition for traverable object is correct
		if ($isTraversableObjectType === TRUE && class_exists($propertyInformation['elementType'])) {
				// @todo: Implement traversable object validator
			$traversableObjectValidator = $this->validatorResolver->createValidator('TraversableObject');
				// redirect the propertyName to the traversable validator
			$traversableObjectValidator->setPropertyName($propertyName);
			$genericValidator->addPropertyValidator($propertyName, $traversableObjectValidator);

		} elseif (isset($propertyInformation['type']) && class_exists($propertyInformation['type'])) {

			/**
			 * Temporary workaround to fix "Fatal error: Maximum function nesting level of '200' reached, aborting!"
			 * in Tx_LwEnetMultipleActionForms_Validation_NestedObjectValidatorResolver->buildNestedObjectsBaseValidators( )
			 * @todo: investigate validation behaviour of broken objects
			 */
			$validationChainBrokenObjects = array(
				'Tx_LwSmaLogin_Domain_Model_User',
				'Tx_LwSmaLogin_Domain_Model_Address'
			);
			if (!in_array($propertyInformation['type'], $validationChainBrokenObjects)) {
					// Build base validator conjunction for type
				$genericValidator->addPropertyValidator($propertyName, $this->validatorResolver->getBaseValidatorConjunction($propertyInformation['type']));
			}
		}
	}

}

