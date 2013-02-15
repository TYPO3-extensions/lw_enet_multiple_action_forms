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
class Tx_LwEnetMultipleActionForms_Session_Typo3SessionAdapter implements Tx_LwEnetMultipleActionForms_Session_AdapterInterface {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var array
	 */
	protected $sessionData;

	/**
	 * @var string
	 */
	protected $sessionKey;

	/**
	 * @var string
	 */
	protected $sessionContext;

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
	 * Injects the configuration manager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * initializeObject
	 *
	 * @return void
	 */
	public function initializeObject() {
		$frameworkConfiguration = $this->configurationManager->getConfiguration(
			Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		if (strlen($frameworkConfiguration['extensionName']) < 1) {
			throw new Exception(
				'No extension name found',
				1323284654
			);
		}
		$this->sessionKey = $frameworkConfiguration['extensionName'];

		if (TYPO3_MODE === 'BE' || TYPO3_MODE === 'FE') {
			$this->sessionContext = TYPO3_MODE;
		} else {
			throw new Exception(
				'Not a valid TYPO3 mode set.',
				1325707890
			);
		}
	}

	/**
	 * @param string $sessionKey
	 */
	public function setSessionKey($sessionKey) {
		$this->sessionKey = $sessionKey;
	}

	/**
	 * store
	 *
	 * @param string $key
	 * @param mixed $data
	 * @return void
	 */
	public function store($key, $data) {
		$this->sessionData[$key] = $data;
		if ($this->sessionContext === 'BE') {
			$GLOBALS['BE_USER']->setAndSaveSessionData($this->sessionKey, $this->sessionData);
		} else {
			$GLOBALS['TSFE']->fe_user->setKey('ses', $this->sessionKey, $this->sessionData);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
		}
	}

	/**
	 * load
	 *
	 * @param string $key
	 * @return array $this->sessionData
	 */
	public function load($key = NULL) {
		if ($this->sessionContext === 'BE') {
			$this->sessionData = $GLOBALS['BE_USER']->getSessionData($this->sessionKey);
		} else {
			$this->sessionData = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->sessionKey);
		}

		if (!is_null($key)) {
			$data = $this->sessionData[$key];
		} else {
			$data = $this->sessionData;
		}
		return $data;
	}

	/**
	 * clear
	 *
	 * @return void
	 */
	public function clear() {
		$this->sessionData = array();
		if ($this->sessionContext === 'BE') {
			$GLOBALS['BE_USER']->setAndSaveSessionData($this->sessionKey, $this->sessionData);
		} else {
			$GLOBALS['TSFE']->fe_user->setKey('ses', $this->sessionKey, $this->sessionData);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
		}
	}

}
