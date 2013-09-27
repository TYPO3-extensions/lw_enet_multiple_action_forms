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
class Tx_LwEnetMultipleActionForms_MVC_Web_BackendRequestHandler extends Tx_Extbase_MVC_Web_BackendRequestHandler {

	/**
	 * @var Tx_Extbase_MVC_Web_Request
	 */
	protected $request;

	/**
	 * @var Tx_LwEnetMultipleActionForms_Session_BackendSessionAdapter
	 */
	protected $sessionAdapter;

	/**
	 * Injects session adapter
	 *
	 * @param Tx_LwEnetMultipleActionForms_Session_AdapterInterface $sessionAdapter
	 */
	public function injectSessionAdapter(Tx_LwEnetMultipleActionForms_Session_AdapterInterface $sessionAdapter) {
		$this->sessionAdapter = $sessionAdapter;
	}

	/**
	 * Handles the web request. The response will automatically be sent to the client.
	 *
	 * @return Tx_Extbase_MVC_Web_Response
	 */
	public function handleRequest() {
			/** @var $requestHashService Tx_Extbase_Security_Channel_RequestHashService */
		$requestHashService = $this->objectManager->get('Tx_Extbase_Security_Channel_RequestHashService'); // singleton
		$requestHashService->verifyRequest($this->request);

			/** @var $response Tx_Extbase_MVC_Web_Response */
		$response = $this->objectManager->create('Tx_Extbase_MVC_Web_Response');

		if ($this->request->hasReferrerArgument()) {
			$persistedControllerData = $this->sessionAdapter->load($this->request->getControllerObjectName());
			if (count($persistedControllerData) > 0) {
				foreach ($persistedControllerData['Arguments'] as $argumentName => $argumentValue) {
					if ($this->request->hasArgument($argumentName)) {
						$argument = $this->request->getArgument($argumentName);
						$argument = t3lib_div::array_merge_recursive_overrule(
							$argumentValue,
							$argument
						);
						$this->request->setArgument($argumentName, $argument);
					} else {
						// @todo: is this case needed anymore???
						$this->request->setHmacVerified(TRUE);
						$this->request->setArgument($argumentName, $argumentValue);
					}
				}
			}
		} else {
			$this->sessionAdapter->clear();
		}

		$this->dispatcher->dispatch($this->request, $response);

		return $response;
	}

	/**
	 * This request handler can handle a web request invoked by the backend.
	 *
	 * @return boolean If we are in backend mode TRUE otherwise FALSE
	 */
	public function canHandleRequest() {
		$this->request = $this->requestBuilder->build();
		$isMultipleActionFormsController = is_subclass_of(
			$this->request->getControllerObjectName(),
			'Tx_LwEnetMultipleActionForms_MVC_Controller_AbstractController'
		);
		return (TYPO3_MODE === 'BE') && ($isMultipleActionFormsController === TRUE);
	}

	/**
	 * Returns the priority - how eager the handler is to actually handle the
	 * request.
	 *
	 * @return integer The priority of the request handler.
	 */
	public function getPriority() {
		return 110;
	}

}
?>