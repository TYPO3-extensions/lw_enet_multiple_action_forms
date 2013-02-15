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
class Tx_LwEnetMultipleActionForms_MVC_Controller_Action {

	/**
	 * @var Tx_Extbase_Reflection_MethodReflection
	 */
	protected $methodReflection;

	/**
	 * name
	 * @var string
	 */
	protected $name;

	/**
	 * index
	 * @var integer
	 */
	protected $index;

	/**
	 * omitted
	 * @var boolean
	 */
	protected $omitted;

	/**
	 * Final action
	 *
	 * @var boolean
	 */
	protected $finalAction;

	/**
	 * @param string $name
	 * @param integer $index
	 * @param Tx_Extbase_Reflection_MethodReflection $methodReflection
	 * @return Tx_LwEnetMultipleActionForms_MVC_Controller_Action
	 */
	public function __construct($name, $index, Tx_Extbase_Reflection_MethodReflection $methodReflection) {
		$this->name = $name;
		$this->index = (int)$index;
		$this->methodReflection = $methodReflection;
		$this->omitted = FALSE;
	}

	/**
	 * Getter for methodReflection
	 *
	 * @return Tx_Extbase_Reflection_MethodReflection methodReflection
	 */
	public function getMethodReflection() {
		return $this->methodReflection;
	}

	/**
	 * Getter for name
	 *
	 * @return string name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Getter for index
	 *
	 * @return integer index
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * Setter for omitted
	 *
	 * @param boolean $omitted
	 * @return void
	 */
	public function setOmitted($omitted) {
		$this->omitted = $omitted;
	}

	/**
	 * Getter for omitted
	 *
	 * @return boolean omitted
	 */
	public function getOmitted() {
		return $this->omitted;
	}

	/**
	 * Getter for actionMethodName
	 *
	 * @return string actionMethodName
	 */
	public function getActionMethodName() {
		return $this->name . 'Action';
	}

	/**
	 * @return bool
	 */
	public function isPreviewAction() {
		return $this->methodReflection->isTaggedWith('previewAction');
	}

	/**
	 * @return bool
	 */
	public function isFinalAction() {
		if (isset($this->finalAction)) {
			return $this->finalAction;
		} else {
			return $this->methodReflection->isTaggedWith('finalAction');
		}
	}

	/**
	 * Possibility to overrule final action
	 *
	 * @param boolan $finalAction
	 */
	public function setFinalAction($finalAction) {
		$this->finalAction = $finalAction;
	}

}
?>