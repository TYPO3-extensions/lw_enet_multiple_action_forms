<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Lars Trebing <lars.trebing@e-net.info>
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
 *
 * @package lw_enet_multiple_action_forms
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_LwEnetMultipleActionForms_MVC_Web_Request extends Tx_Extbase_MVC_Web_Request {

	/**
	 * Checks if the __referrer argument exists (is set) and of type array
	 *
	 * @return boolean TRUE if the referrer is set and of type array, otherwise FALSE
	 */
	public function hasReferrerArgument() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(t3lib_extMgm::getExtensionVersion('extbase')) < t3lib_utility_VersionNumber::convertVersionNumberToInteger('1.4.0')) {
			return $this->hasArgument('__referrer') && is_array($this->getArgument('__referrer'));
		} else {
			return is_array($this->getInternalArgument('__referrer'));
		}
	}

	/**
	 * Returns the value of the __referrer argument
	 *
	 * @return mixed Value of the argument, or NULL if not set or not an array
	 */
	public function getReferrerArgument() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(t3lib_extMgm::getExtensionVersion('extbase')) < t3lib_utility_VersionNumber::convertVersionNumberToInteger('1.4.0')) {
			$referrer = $this->hasArgument('__referrer') ? $this->getArgument('__referrer') : NULL;
		} else {
			$referrer = $this->getInternalArgument('__referrer');
		}
		if (is_array($referrer)) {
			return $referrer;
		} else {
			return NULL;
		}
	}

}
?>