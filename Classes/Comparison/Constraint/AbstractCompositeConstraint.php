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
 * @subpackage Comparison|Constraint
 * @scope prototype
 *
 */
abstract class Tx_LwEnetMultipleActionForms_Comparison_Constraint_AbstractCompositeConstraint implements Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface, Countable {

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var Tx_Extbase_Persistence_ObjectStorage
	 */
	protected $constraints;

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Constructs the validator conjunction
	 *
	 */
	public function __construct() {
		$this->constraints = new Tx_Extbase_Persistence_ObjectStorage();
	}

	/**
	 * Does nothing.
	 *
	 * @param array $options Not used
	 * @return void
	 */
	public function setOptions(array $options) {
	}

	/**
	 * Returns an array of errors which occurred during the last isValid() call.
	 *
	 * @return array An array of Tx_Extbase_Validation_Error objects or an empty array if no errors occurred.
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Adds a new validator to the conjunction.
	 *
	 * @param Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface $constraint The validator that should be added
	 * @return void
	 */
	public function addConstraint(Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface $constraint) {
		$this->constraints->attach($constraint);
	}

	/**
	 * Removes the specified validator.
	 *
	 * @param Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface $constraint The validator to remove
	 */
	public function removeConstraint(Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface $constraint) {
		if (!$this->constraints->contains($constraint)) {
			 throw new Tx_LwEnetMultipleActionForms_MVC_Controller_Action_Exception_NoSuchConstraint(
				 'Cannot remove override condition because its not in the conjunction.',
				 1322226420
			 );
		}
		$this->constraints->detach($constraint);
	}

	/**
	 * Returns the number of validators contained in this conjunction.
	 *
	 * @return integer The number of validators
	 */
	public function count() {
		return count($this->constraints);
	}
}

?>