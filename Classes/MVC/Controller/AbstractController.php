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
abstract class Tx_LwEnetMultipleActionForms_MVC_Controller_AbstractController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var string
	 */
	protected $sessionPersistenceInterface = 'Tx_LwEnetMultipleActionForms_Session_PersistenceInterface';

	/**
	 * @var boolean True if action manually persisted the arguments already
	 */
	protected $argumentsPersisted = FALSE;

	/**
	 * @var Tx_LwEnetMultipleActionForms_Service_ActionSequence
	 */
	protected $actionSequenceService;

	/**
	 * @var Tx_LwEnetMultipleActionForms_MVC_Controller_Action_Override_ConstraintResolver
	 */
	protected $actionOverrideConstraintResolver;

	/**
	 * @var Tx_LwEnetMultipleActionForms_Session_AdapterInterface
	 */
	protected $sessionAdapter;

	/**
	 * @var array<Tx_LwEnetMultipleActionForms_Comparison_Constraint_ConstraintInterface>
	 */
	protected $actionOverrideConstraints;

	/**
	 * Injects the action sequence service
	 *
	 * @param Tx_LwEnetMultipleActionForms_Service_ActionSequence $actionSequenceService
	 * @return void
	 */
	public function injectActionSequenceService(Tx_LwEnetMultipleActionForms_Service_ActionSequence $actionSequenceService) {
		$this->actionSequenceService = $actionSequenceService;
	}

	/**
	 * Injects the validator resolver
	 *
	 * @param Tx_LwEnetMultipleActionForms_MVC_Controller_Action_Override_ConstraintResolver $actionOverrideConstraintResolver
	 * @return void
	 */
	public function injectOverrideActionConstraintResolver(Tx_LwEnetMultipleActionForms_MVC_Controller_Action_Override_ConstraintResolver $actionOverrideConstraintResolver) {
		$this->actionOverrideConstraintResolver = $actionOverrideConstraintResolver;
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
	 * @param $actionMethodName
	 * @return void
	 */
	protected function initializeActionMethodOverrideConditions($actionMethodName) {
		$this->actionOverrideConstraints = $this->actionOverrideConstraintResolver->buildMethodArgumentsOverrideActionConstraints(
			get_class($this), $actionMethodName
		);
	}

	/**
	 * Handles a request. The result output is returned by altering the given response.
	 *
	 * @param Tx_Extbase_MVC_RequestInterface $request The request object
	 * @param Tx_Extbase_MVC_ResponseInterface $response The response, modified by this handler
	 * @return void
	 */
	public function processRequest(Tx_Extbase_MVC_RequestInterface $request, Tx_Extbase_MVC_ResponseInterface $response) {
		$this->actionSequenceService->initialize($this, $request);
		parent::processRequest($request, $response);
	}

	/**
	 * Resolves and checks the current action method name
	 *
	 * @return string Method name of the current action
	 * @throws Tx_Extbase_MVC_Exception_NoSuchAction if the action specified in the request object does not exist (and if there's no default action either).
	 */
	protected function resolveActionMethodName() {
		$actionMethodName = $this->actionSequenceService->getCurrentAction()->getActionMethodName();
		$this->initializeActionMethodOverrideConditions($actionMethodName);

		/** @var $argument Tx_Extbase_MVC_Controller_Argument */
		foreach ($this->request->getArguments() as $argumentName => $argumentValue) {
			if (!is_null($this->actionOverrideConstraints[$argumentName])) {
				if ($this->actionOverrideConstraints[$argumentName]->isComplied($argumentValue) === TRUE) {

						// Forward to next action if this action is to be omitted
					$nextAction = $this->actionSequenceService->getNextAction();
					if ($actionMethodName !== $nextAction->getActionMethodName()) {
						$actionMethodName = $nextAction->getActionMethodName();
						$this->actionSequenceService->getCurrentAction()->setOmitted(TRUE);
						$this->actionSequenceService->persistOmittedActions();
						$this->forward(
							$this->actionSequenceService->getNextAction()->getName(),
							$this->request->getControllerName(),
							$this->request->getControllerExtensionName(),
							$this->request->getArguments()
						);
					} else {
						 throw new Tx_LwEnetMultipleActionForms_MVC_Controller_Action_Override_Exception_InvalidActionMethod(
							 'Can\'t resolve action "' . $actionMethodName . '" does not exist in controller "' . get_class($this) . '".',
							 1322231384
						 );
					}
				}
			}
		}

		if (!method_exists($this, $actionMethodName)) {
			 throw new Tx_Extbase_MVC_Exception_NoSuchAction(
				 'An action "' . $actionMethodName . '" does not exist in controller "' . get_class($this) . '".',
				 1186669086
			 );
		}

			// Here should the persisted properties be dropped
		$this->dropObsoleteProperties($actionMethodName);

		return $actionMethodName;
	}


	/**
	 * @param string $actionMethodName
	 */
	protected function dropObsoleteProperties($actionMethodName) {
		$persistedControllerData = $this->sessionAdapter->load($this->request->getControllerObjectName());

			/** @var $methodReflection Tx_Extbase_Reflection_MethodReflection */
		$methodReflection = $this->objectManager->get(
			'Tx_Extbase_Reflection_MethodReflection',
			get_class($this),
			$actionMethodName
		);

			/** @var $parameterReflection Tx_Extbase_Reflection_ParameterReflection */
		foreach ($methodReflection->getParameters() as $parameterReflection) {
				// @todo: check for implementation of interface Tx_LwEnetMultipleActionForms_Session_PersistenceInterface
			if (!isset($persistedControllerData['Arguments'][$parameterReflection->getName()])) {
				continue;
			} else {
				$persistedArgument = $persistedControllerData['Arguments'][$parameterReflection->getName()];
			}

			if ($this->request->hasArgument($parameterReflection->getName()) ) {
				$argument = $this->request->getArgument($parameterReflection->getName());

					/** @var $classReflection Tx_Extbase_Reflection_ClassReflection */
				$classReflection = $parameterReflection->getClass();
				$propertyDependencies = $this->buildPropertyDependencies($parameterReflection->getClass());

				foreach ($propertyDependencies as $propertyName => $dependentPropertyNames) {
					if (isset($persistedArgument[$propertyName]) && isset($argument[$propertyName])) {
							// @todo: check different types
							// Compare property values
							// Warning: This is no type safe comparison by intention:
							// 		if for example a product uid is persisted as int, the incoming
							// 		POST set is a string. If the value itself is not changed, it
							// 		should not be dropped
						if ($persistedArgument[$propertyName] != $argument[$propertyName]) {
							foreach ($dependentPropertyNames as $dependentPropertyName) {
								unset($argument[$dependentPropertyName]);
							}
						}
					}
				}
				$this->request->setArgument($parameterReflection->getName(), $argument);
			}
		}
	}

	/**
	 * @param Tx_Extbase_Reflection_ClassReflection $classReflection
	 * @return array
	 */
	protected function buildPropertyDependencies(Tx_Extbase_Reflection_ClassReflection $classReflection) {
		$propertyDependencies = array();
			/** @var $propertyReflection Tx_Extbase_Reflection_PropertyReflection */
		foreach ($classReflection->getProperties() as $propertyReflection) {
			if ($propertyReflection->isTaggedWith('dependency')) {
				$tagValues = $propertyReflection->getTagValues('dependency');
				foreach ($tagValues as $tagValue) {
					$propertyToBeChanged = ltrim($tagValue, '$');
					// propertyToBeChanged => propertiesToBeDropped
					$propertyDependencies[$propertyToBeChanged][] = $propertyReflection->getName();
				}
			}
		}

			// @todo; Add recursive relation processing
			// build related dependencies
		foreach ($propertyDependencies as $propertyName => $propertyDependency) {
			foreach ($propertyDependency as $dependentProperty) {
				if (array_key_exists($dependentProperty, $propertyDependencies)) {
					$propertyDependencies[$propertyName] = array_merge(
						$propertyDependencies[$dependentProperty],
						$propertyDependency
					);
				}
			}
		}

		return $propertyDependencies;
	}

	/**
	 * Calls the specified action method and passes the arguments.
	 *
	 * If the action returns a string, it is appended to the content in the
	 * response object. If the action doesn't return anything and a valid
	 * view exists, the view is rendered automatically.
	 *
	 * @return void
	 * @api
	 */
	protected function callActionMethod() {
		$this->view->assign('actionSequenceData', $this->actionSequenceService->toArray());

			// @deprecated since Extbase 1.4.0, will be removed with Extbase 1.6.0.
		$preparedArguments = array();

		/** @var $argument Tx_Extbase_MVC_Controller_Argument */
		foreach ($this->arguments as $argument) {
			$preparedArguments[$argument->getName()] = $argument->getValue();
		}

		if ($this->argumentsMappingResults->hasErrors()) {
			$this->actionSequenceService->persistOmittedActions();
			$actionResult = call_user_func(
				array($this, $this->errorMethodName)
			);
		} else {
			$actionResult = call_user_func_array(
				array($this, $this->actionMethodName),
				$preparedArguments
			);
			if ($this->actionSequenceService->getCurrentAction()->isFinalAction()) {
				$this->sessionAdapter->clear();
			} else {
				if (!$this->argumentsPersisted) {
					$this->persistArgumentsWithReferrerCheck($preparedArguments);
					$this->argumentsPersisted = TRUE;
				}
			}
		}

		if ($actionResult === NULL && $this->view instanceof Tx_Extbase_MVC_View_ViewInterface) {
			$this->response->appendContent($this->view->render());
		} elseif (is_string($actionResult) && strlen($actionResult) > 0) {
			$this->response->appendContent($actionResult);
		} elseif (is_object($actionResult) && method_exists($actionResult, '__toString')) {
			$this->response->appendContent((string)$actionResult);
		}
	}

	/**
	 * Persist arguments with additional referrer test
	 *
	 * @param array $arguments
	 * @return void
	 */
	public function persistArgumentsWithReferrerCheck(array $arguments) {
		if (!$this->request->hasReferrerArgument()) {
			return;
		}

		$this->persistArguments($arguments);
	}

	/**
	 * persist arguments in session
	 *
	 * @param array $arguments
	 * @return void
	 */
	public function persistArguments(array $arguments) {
		foreach ($arguments as $argumentName => $argumentValue) {
			if ($this->isObjectImplementingSessionPersistenceInterface($argumentValue)) {
				$propertyData = array(
					$argumentName => $this->convertObjectToArrayRecursively($argumentValue)
				);

				$this->storeSessionData('Arguments', $propertyData);
			}
		}
	}

	/**
	 * @param Tx_LwEnetMultipleActionForms_Session_PersistenceInterface $sessionPersistenceObject
	 * @return array
	 */
	protected function convertObjectToArrayRecursively(Tx_LwEnetMultipleActionForms_Session_PersistenceInterface $sessionPersistenceObject) {
		$objectData = array();
		/** @var $reflectionService Tx_LwEnetMultipleActionForms_Service_Reflection */
		$reflectionService = $this->objectManager->get('Tx_LwEnetMultipleActionForms_Service_Reflection');
		foreach ($reflectionService->getClassPropertiesToPersistInSession($sessionPersistenceObject) as $propertyName) {
			$propertyValue = call_user_func(
				array($sessionPersistenceObject, 'get' . ucfirst($propertyName))
			);
			if (isset($propertyValue) === TRUE) {
				if (is_object($propertyValue)) {
					if ($this->isObjectImplementingSessionPersistenceInterface($propertyValue) === TRUE) {
						$nestedObjectData = $this->convertObjectToArrayRecursively($propertyValue);
						if (count($nestedObjectData) > 0) {
							$objectData[$propertyName] = $nestedObjectData;
						}
					} elseif ($this->isObjectImplementingDomainObjectInterface($propertyValue) === TRUE) {
						$objectData[$propertyName] = $propertyValue->getUid();
					} elseif ($this->isTraversableObjectProperty($sessionPersistenceObject, $propertyName) === TRUE) {
						foreach ($propertyValue as $object) {
							if ($this->isObjectImplementingSessionPersistenceInterface($object)) {
								$objectData[$propertyName][] = $this->convertObjectToArrayRecursively($object);
							} elseif ($this->isObjectImplementingDomainObjectInterface($object) === TRUE) {
								$objectData[$propertyName][] = $object->getUid();
							} else {
								debug('Not handled child object!');
							}
						}
					} elseif ($propertyValue instanceof DateTime) {
						$objectData[$propertyName] = $propertyValue->format('c');
					} else {
						debug('Not handled object');
						// Not handled objects
					}
				} else {
					$objectData[$propertyName] = $propertyValue;
				}
			}
		}
		return $objectData;
	}

	/**
	 * Store session data
	 *
	 * @param string $key
	 * @param mixed $data
	 * @return void
	 */
	protected function storeSessionData($key, $data) {
		$className = get_class($this);
		$sessionData = $this->sessionAdapter->load($className);
		$sessionData[$key] = $data;
		$this->sessionAdapter->store($className, $sessionData);
	}

	/**
	 * @param object $object
	 * @return bool
	 */
	protected function isObjectImplementingSessionPersistenceInterface($object) {
		if (is_object($object)) {
			$result = in_array(
				Tx_LwEnetMultipleActionForms_Session_AdapterInterface::SESSION_PERSISTENCE_INTERFACE,
				class_implements($object)
			);
		} else {
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * @param object $object
	 * @return bool
	 */
	protected function isObjectImplementingDomainObjectInterface($object) {
		if (is_object($object)) {
			$result = in_array(
				'Tx_Extbase_DomainObject_DomainObjectInterface',
				class_implements($object)
			);
		} else {
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * @param object $object
	 * @param string $propertyName
	 * @return bool
	 */
	protected function isTraversableObjectProperty($object, $propertyName) {
		if (is_object($object)) {
			/** @var $classSchema Tx_Extbase_Reflection_ClassSchema */
			$classSchema = $this->reflectionService->getClassSchema($object);
			if ($classSchema->hasProperty($propertyName)) {
				$propertyInformation = $classSchema->getProperty($propertyName);
				$result = in_array(
					$propertyInformation['type'],
					array('array', 'ArrayObject', 'SplObjectStorage', 'Tx_Extbase_Persistence_ObjectStorage')
				);
			} else {
				$result = FALSE;
			}
		} else {
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * A special action which is called if the originally intended action could
	 * not be called, for example if the arguments were not valid.
	 *
	 * The default implementation sets a flash message, request errors and forwards back
	 * to the originating action. This is suitable for most actions dealing with form input.
	 *
	 * We clear the page cache by default on an error as well, as we need to make sure the
	 * data is re-evaluated when the user changes something.
	 *
	 * @return string
	 * @api
	 */
	protected function errorAction() {
		$this->request->setErrors($this->argumentsMappingResults->getErrors());
		$this->clearCacheOnError();

		$errorFlashMessage = $this->getErrorFlashMessage();
		if ($errorFlashMessage !== FALSE) {
			$this->flashMessageContainer->add($errorFlashMessage, '', t3lib_FlashMessage::ERROR);
		}

		$sessionData = $this->sessionAdapter->load(get_class($this));

		$refererIsFirstAction = FALSE;
		if ($this->request->hasArgument('__referrer')) {
			$refererAction = $this->actionSequenceService->getReferrerAction();
			if ($refererAction instanceof Tx_LwEnetMultipleActionForms_MVC_Controller_Action && $refererAction->getIndex() === 0) {
				$refererIsFirstAction = TRUE;
			}
		}

		/*
		 * test sessionError
		 * 1) no arguments in session
		 * 2) we are not no first action
		 * 3) referer is not first action
		 */
		if (is_array($sessionData['Arguments']) === FALSE && $this->actionSequenceService->isFirstAction() === FALSE && $refererIsFirstAction === FALSE) {
			if (isset($this->settings['errorPidOnSessionErrors'])) {
				$pageUid = (int)$this->settings['errorPidOnSessionErrors'];
				$uriBuilder = $this->controllerContext->getUriBuilder();
				$uri = $uriBuilder->setTargetPageUid($pageUid)->build();
				$this->redirectToURI($uri);
			} else {
				$this->flashMessageContainer->flush();
				$this->flashMessageContainer->add(
					Tx_Extbase_Utility_Localization::translate(
						'errorAction.sessionError.message',
						'LwEnetMultipleActionForms'
					),
					Tx_Extbase_Utility_Localization::translate(
						'errorAction.sessionError.title',
						'LwEnetMultipleActionForms'
					),
					t3lib_FlashMessage::WARNING
				);

				$this->redirect(
					$this->actionSequenceService->getFirstAction()->getName(),
					$this->request->getControllerName(),
					$this->request->getControllerExtensionName()
				);
			}
		}

		if ($this->request->hasReferrerArgument()) {
			$referrer = $this->request->getReferrerArgument();
			$this->forward($referrer['actionName'], $referrer['controllerName'], $referrer['extensionName'], $this->request->getArguments());
		}

		$this->redirect(
			array_shift($this->actionSequenceService->getSequence()),
			$this->request->getControllerName(),
			$this->request->getControllerExtensionName()
		);
	}

	/**
	 * @return string
	 */
	public function getActionMethodName() {
		return $this->request->getControllerActionName() . 'Action';
	}

}
?>
