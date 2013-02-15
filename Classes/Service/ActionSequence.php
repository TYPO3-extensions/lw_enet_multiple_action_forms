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
class Tx_LwEnetMultipleActionForms_Service_ActionSequence implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_MVC_RequestInterface
	 */
	protected $request;

	/**
	 * @var array<Tx_LwEnetMultipleActionForms_MVC_Controller_Action>
	 */
	protected $sequence;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @var Tx_Extbase_Reflection_ClassReflection
	 */
	protected $controllerReflection;

	/**
	 * @var Tx_LwEnetMultipleActionForms_Session_AdapterInterface
	 */
	protected $sessionAdapter;

	/**
	 * @var array
	 */
	protected $omittedActions;

	/**
	 * @param Tx_LwEnetMultipleActionForms_MVC_Controller_AbstractController $controller
	 * @param  Tx_Extbase_MVC_RequestInterface $request
	 * @return Tx_LwEnetMultipleActionForms_Service_ActionSequence
	 * @throws Tx_LwEnetMultipleActionForms_MVC_Controller_Exception_ActionSequenceAnnotationMissing
	 */
	public function initialize(Tx_LwEnetMultipleActionForms_MVC_Controller_AbstractController $controller, Tx_Extbase_MVC_RequestInterface $request) {
		$this->request = $request;

		/** @var $classReflection Tx_Extbase_Reflection_ClassReflection */
		$this->controllerReflection = $this->objectManager->get(
			'Tx_Extbase_Reflection_ClassReflection',
			$controller
		);

		if ($this->controllerReflection->isTaggedWith('actionSequence')) {
			$actionSequenceValues = $this->controllerReflection->getTagValues('actionSequence');
			$actions = t3lib_div::trimExplode(
				',',
				array_shift($actionSequenceValues),
				TRUE
			);

			foreach ($actions as $actionIndex => $actionName) {
				$actionMethodName = $actionName . 'Action';
				if ($this->controllerReflection->hasMethod($actionMethodName)) {
					/** @var $action Tx_LwEnetMultipleActionForms_MVC_Controller_Action */
					$action = $this->objectManager->get(
						'Tx_LwEnetMultipleActionForms_MVC_Controller_Action',
						$actionName,
						$actionIndex,
						$this->controllerReflection->getMethod($actionMethodName)
					);
					$this->sequence[$actionIndex] = $action;
				} else {
					throw new Tx_Extbase_MVC_Exception_NoSuchAction(
						'No such action in Controller: ' . get_class($controller) . '->' . $actionMethodName,
						1322527814
					);
				}
			}

		} else {
			throw new Tx_LwEnetMultipleActionForms_MVC_Controller_Exception_ActionSequenceAnnotationMissing(
				'ActionSequence annotation missing in Controller: ' . get_class($this),
				1322141888
			);
		}

		if ($this->getReferrerAction() instanceof Tx_LwEnetMultipleActionForms_MVC_Controller_Action) {
			if ($this->getReferrerAction()->getIndex() > $this->getCurrentAction()->getIndex()) {
				$this->resetOmittedActions();
			}
		}
	}

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
	 * Injects the reflection service
	 *
	 * @param Tx_Extbase_Reflection_Service $reflectionService
	 * @return void
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Injects session adapter
	 *
	 * @param Tx_LwEnetMultipleActionForms_Session_AdapterInterface $sessionAdapter
	 */
	public function injectSessionAdapter(Tx_LwEnetMultipleActionForms_Session_AdapterInterface $sessionAdapter) {
		$this->sessionAdapter = $sessionAdapter;
	}

	/**
	 * @return void
	 * @throws Tx_LwEnetMultipleActionForms_Controller_Exception
	 */
	public function initializeObject() {

	}

	/**
	 * Getter for sequence
	 *
	 * @return array sequence
	 */
	public function getSequence() {
		return $this->sequence;
	}

	/**
	 * Getter for request
	 *
	 * @return Tx_Extbase_MVC_RequestInterface request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Setter for controllerReflection
	 *
	 * @param Tx_Extbase_Reflection_ClassReflection $controllerReflection
	 * @return void
	 */
	public function setControllerReflection(Tx_Extbase_Reflection_ClassReflection $controllerReflection) {
		$this->controllerReflection = $controllerReflection;
	}

	/**
	 * Getter for controllerReflection
	 *
	 * @return Tx_Extbase_Reflection_ClassReflection controllerReflection
	 */
	public function getControllerReflection() {
		return $this->controllerReflection;
	}

	/**
	 * @return bool
	 */
	public function isFirstAction() {
		/** @var $firstAction Tx_LwEnetMultipleActionForms_MVC_Controller_Action */
		$firstAction = reset($this->sequence);
		return ($firstAction == $this->getCurrentAction());
	}

	/**
	 * @return bool
	 */
	public function isLastAction() {
		/** @var $lastAction Tx_LwEnetMultipleActionForms_MVC_Controller_Action */
		$lastAction = end($this->sequence);
		return ($lastAction == $this->getCurrentAction());
	}

	/**
	 * @return Tx_LwEnetMultipleActionForms_MVC_Controller_Action
	 */
	public function getCurrentAction() {
		return $this->getActionByActionName($this->request->getControllerActionName());
	}

	/**
	 * @return integer
	 * @deprecated
	 */
	public function getCurrentActionIndex() {
		$result = array_search($this->getCurrentAction(), $this->sequence);
		if ($result === FALSE) {
			$result = 0;
		}
		return (int)$result;
	}

	/**
	 * @return Tx_LwEnetMultipleActionForms_MVC_Controller_Action
	 */
	public function getReferrerAction() {
		$referrer = NULL;
		if ($this->request->hasArgument('__referrer')) {
			$referrer = $this->request->getArgument('__referrer');
		}
		return $this->getActionByActionName($referrer['actionName']);
	}

	public function getFirstAction() {
		$actions = $this->getSequence();
		return $actions[0];
	}

	/**
	 * @return Tx_LwEnetMultipleActionForms_MVC_Controller_Action
	 */
	public function getPreviousAction() {
		$previousAction = $this->getCurrentAction();
		$omittedActions = $this->getOmittedActions();
		$actionIndex = $this->getCurrentActionIndex() - 1;
		while (!is_null($this->sequence[$actionIndex])) {
			/** @var $action Tx_LwEnetMultipleActionForms_MVC_Controller_Action */
			$action = $this->sequence[$actionIndex];
			if (!in_array($action->getName(), $omittedActions)) {
				$previousAction = $action;
				break 1;
			}
			$actionIndex--;
		}
		return $previousAction;
	}

	/**
	 * @return Tx_LwEnetMultipleActionForms_MVC_Controller_Action
	 */
	public function getNextAction() {
		$nextAction = $this->getCurrentAction();
		$result = array_search($this->getCurrentAction(), $this->sequence);
		if ($result !== FALSE) {
			if (!is_null($this->sequence[($result + 1)])) {
				$nextAction = $this->sequence[($result + 1)];
			}
		}
		return $nextAction;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		$actionFlowData = array(
			'actionSequence' => $this->getSequence(),
			'actionCount' => count($this->getSequence()),
			'previousAction' => $this->getPreviousAction(),
			'currentAction' => $this->getCurrentAction(),
			'nextAction' => $this->getNextAction(),
			'isFirstAction' => $this->isFirstAction(),
			'isLastAction' => $this->isLastAction(),
			'isPreviewAction' => $this->getCurrentAction()->isPreviewAction(),
			'isFinalAction' => $this->getCurrentAction()->isFinalAction(),
		);
		return $actionFlowData;
	}

	/**
	 * @param $actionName
	 * @return int
	 */
	public function getActionIndexByActionName($actionName) {
		$actionIndex = NULL;
		/** @var $action Tx_LwEnetMultipleActionForms_MVC_Controller_Action */
		foreach ($this->sequence as $actionIndex => $action) {
			if ($actionName == $action->getName()) {
				return $actionIndex;
			}
		}
	}

	/**
	 * @param $actionName
	 * @return Tx_LwEnetMultipleActionForms_MVC_Controller_Action
	 */
	public function getActionByActionName($actionName) {
		/** @var $action Tx_LwEnetMultipleActionForms_MVC_Controller_Action */
		foreach ($this->sequence as $actionIndex => $action) {
			if ($actionName == $action->getName()) {
				return $action;
			}
		}
	}

	/**
	 * persist
	 *
	 * @return void
	 */
	public function persistOmittedActions() {
		$omittedActions = array();
		/** @var $action Tx_LwEnetMultipleActionForms_MVC_Controller_Action */
		foreach ($this->sequence as $action) {
			if ($action->getOmitted() === TRUE) {
				$omittedActions[$action->getIndex()] = $action->getName();
			}
		}
		$this->storeOmittedActionsInSession($omittedActions);
	}

	/**
	 * @param array $omittedActions
	 * @return void
	 */
	public function updateSequence(array $omittedActions) {
		/** @var $action Tx_LwEnetMultipleActionForms_MVC_Controller_Action */
		foreach ($this->sequence as $actionIndex => $action) {
			if (in_array($action->getName(), $omittedActions)) {
				$action->setOmitted(TRUE);
			} else {
				$action->setOmitted(FALSE);
			}
		}
	}

	/**
	 * persist
	 *
	 * @return void
	 */
	public function resetOmittedActions() {
		$sessionData = $this->sessionAdapter->load($this->request->getControllerObjectName());
		if (is_array($sessionData['OmittedActions'])) {
			foreach ($sessionData['OmittedActions'] as $actionIndex => $omittedActionName) {
				if ($this->getActionIndexByActionName($omittedActionName) >= $this->getCurrentActionIndex()) {
					unset($sessionData['OmittedActions'][$actionIndex]);
				}
			}
		} else {
			$sessionData['OmittedActions'] = array();
		}
		$this->storeOmittedActionsInSession($sessionData['OmittedActions'], FALSE);
	}

	/**
	 * persist
	 *
	 * @return array
	 */
	public function getOmittedActions() {
		$sessionData = $this->sessionAdapter->load($this->request->getControllerObjectName());
		$omittedActions = array();
		if (is_array($sessionData['OmittedActions'])) {
			$omittedActions = $sessionData['OmittedActions'];
		}
		$this->updateSequence($omittedActions);
		return $omittedActions;
	}

	/**
	 * @param array $omittedActions
	 * @param boolean $attachData
	 * @return void
	 */
	protected function storeOmittedActionsInSession(array $omittedActions, $attachData = TRUE) {
		$sessionData = $this->sessionAdapter->load($this->request->getControllerObjectName());
		$key = 'OmittedActions';

		if (!is_array($sessionData[$key])) {
			$sessionData[$key] = array();
		}
		if ($attachData === TRUE) {
			$sessionData[$key] += $omittedActions;
		} else {
			$sessionData[$key] = $omittedActions;
		}
		$this->updateSequence($sessionData[$key]);
		$this->sessionAdapter->store($this->request->getControllerObjectName(), $sessionData);
	}


	/**
	 * @param array $constraints
	 * @return boolean
	 */
	public function isPropertyValidationRequired(array $constraints) {
		$result = TRUE;
		$parsedValidatorConstraintAnnotations = Tx_LwEnetMultipleActionForms_Utility_AnnotationParser::parseAnnotations(
			$constraints
		);

		$propertyValidationActionIndex = NULL;
		foreach ($parsedValidatorConstraintAnnotations as $parsedAnnotation) {
			if (class_exists($parsedAnnotation['className'])) {
				$controllerObject = $this->objectManager->get($this->getRequest()->getControllerObjectName());
				if ($controllerObject instanceof $parsedAnnotation['className']) {
					if (!isset($parsedAnnotation['options']['actionIndex'])) {
						$propertyValidationActionIndex = $this->getActionIndexByActionName(
							$parsedAnnotation['options']['actionName']
						);
					} else {
						$propertyValidationActionIndex = (int)$parsedAnnotation['options']['actionIndex'];
					}
					break 1;
				}
			}
		}

		/**
		 * If property validation index is null, set to second action index.
		 * so the @dontvalidate annotation is redundant in the first action
		 */
		if (is_null($propertyValidationActionIndex)) {
			$propertyValidationActionIndex = 1;
		}

		/**
		 * Check if action before property validation action is omitted,
		 * if true no validation is required for this validation action
		 */
		$isActionBeforePropertyValidationActionOmitted = array_key_exists(
			$propertyValidationActionIndex - 1,
			$this->getOmittedActions()
		);

		/**
		 * If current action is before property validation action in action sequence,
		 * no validation is required
		 * although if $isActionBeforePropertyValidationActionOmitted is TRUE no validation is required
		 */
		if ($this->getCurrentAction()->getIndex() < $propertyValidationActionIndex || $isActionBeforePropertyValidationActionOmitted === TRUE) {
			$result = FALSE;
		}
		return $result;
	}
}
?>