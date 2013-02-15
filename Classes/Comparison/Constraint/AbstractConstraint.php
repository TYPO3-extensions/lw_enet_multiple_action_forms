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
abstract class Tx_LwEnetMultipleActionForms_Comparison_Constraint_AbstractConstraint implements Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface {
	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Sets options for the validator
	 *
	 * @param array $options Options for the validator
	 * @return void
	 */
	public function setOptions(array $options) {
		$this->options = $options;
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
	 * Creates a new validation error object and adds it to $this->errors
	 *
	 * @param string $message The error message
	 * @param integer $code The error code (a unix timestamp)
	 * @return void
	 */
	protected function addError($message, $code) {
		$this->errors[] = new Tx_Extbase_Validation_Error($message, $code);
	}
}

?>