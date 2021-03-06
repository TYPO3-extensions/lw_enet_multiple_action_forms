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
 *
 * @package lw_enet_multiple_action_forms
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_LwEnetMultipleActionForms_Service_Reflection {

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

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
	 * @param Tx_LwEnetMultipleActionForms_Session_PersistenceInterface $sessionPersistenceObject
	 * @return array
	 */
	public function getClassPropertiesToPersistInSession(Tx_LwEnetMultipleActionForms_Session_PersistenceInterface $sessionPersistenceObject) {
		$objectName = get_class($sessionPersistenceObject);
		$persistentProperties = array();
		foreach ($this->reflectionService->getClassPropertyNames($objectName) as $propertyName) {
			$isPropertyToPersist = $this->reflectionService->isPropertyTaggedWith(
				$objectName,
				$propertyName,
				'sessionPersist'
			);
			if ($isPropertyToPersist === TRUE) {
				$persistentProperties[] = $propertyName;
			}
		}
		return $persistentProperties;
	}

}
?>