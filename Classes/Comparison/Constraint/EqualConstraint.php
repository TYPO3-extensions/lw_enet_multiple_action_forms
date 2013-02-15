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
 *
 */
class Tx_LwEnetMultipleActionForms_Comparison_Constraint_EqualConstraint extends Tx_LwEnetMultipleActionForms_Comparison_Constraint_AbstractConstraint {

	/**
	 * Returns TRUE, if the given property ($propertyValue) is a valid text (contains no XML tags).
	 *
	 * If at least one error occurred, the result is FALSE.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isComplied($value) {
		$isComplied = FALSE;

		if (isset($this->options['property']) && is_array($value)) {
			$comparisonValue = $value[$this->options['property']];
		} else {
			$comparisonValue = $value;
		}

		if (isset($this->options['value'])) {
			$isComplied = $comparisonValue === $this->options['value'];
		} elseif (isset($this->options['valueList'])) {
			$isComplied = !$this->isValueInList($comparisonValue, $this->options['valueList']);
		} else {
			throw new Tx_LwEnetMultipleActionForms_Comparison_Exception_InvalidConstraintConfiguration(
				'Invalid configuration options set for ' . get_class($this),
				1323181731
			);
		}
		return $isComplied;
	}

	/**
	 * @param string $value
	 * @param string $valueList
	 * @return bool
	 */
	protected function isValueInList($value, $valueList) {
		$valueList = t3lib_div::trimExplode(',', $valueList);
		return !in_array($value, $valueList);
	}
}

?>