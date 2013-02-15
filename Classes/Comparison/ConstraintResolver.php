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
 * @subpackage Comparison
 *
 */
class Tx_LwEnetMultipleActionForms_Comparison_ConstraintResolver implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

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
	 * Get a validator for a given data type. Returns a validator implementing
	 * the Tx_Extbase_Validation_Validator_ValidatorInterface or NULL if no validator
	 * could be resolved.
	 *
	 * @param string $constraintName Either one of the built-in data types or fully qualified validator class name
	 * @param array $constraintOptions Options to be passed to the validator
	 * @return Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface Validator or NULL if none found.
	 */
	public function createConstraint($constraintName, array $constraintOptions = array()) {
		$constraintClassName = $this->resolveConstraintObjectName($constraintName);
		if ($constraintClassName === FALSE) return NULL;
		$constraint = $this->objectManager->get($constraintClassName);
		if (!($constraint instanceof Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface)) {
			return NULL;
		}

		$constraint->setOptions($constraintOptions);
		return $constraint;
	}

	/**
	 *
	 *
	 * Returns an object of an appropriate validator for the given class. If no validator is available
	 * FALSE is returned
	 *
	 * @param string $constraintName Either the fully qualified class name of the validator or the short name of a built-in validator
	 * @return string Name of the validator object or FALSE
	 */
	protected function resolveConstraintObjectName($constraintName) {
		if (strstr($constraintName, '_') !== FALSE && class_exists($constraintName)) return $constraintName;

		$possibleClassName = 'Tx_LwEnetMultipleActionForms_Comparison_Constraint_' . $this->unifyConstraintName($constraintName) . 'Constraint';
		if (class_exists($possibleClassName)) return $possibleClassName;

		return FALSE;
	}

	/**
	 * Preprocess data types. Used to map primitive PHP types to DataTypes used in Extbase.
	 *
	 * @param string $constraintName Data type to unify
	 * @return string unified data type
	 */
	protected function unifyConstraintName($constraintName) {
		return ucfirst($constraintName);
	}

}

?>