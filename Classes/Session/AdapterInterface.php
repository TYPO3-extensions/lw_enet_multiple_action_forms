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
 * @subpackage Session
 *
 */
interface Tx_LwEnetMultipleActionForms_Session_AdapterInterface {

	const SESSION_PERSISTENCE_INTERFACE = 'Tx_LwEnetMultipleActionForms_Session_PersistenceInterface';

	/**
	 * @abstract
	 * @param $key
	 * @param $data
	 * @return void
	 */
	public function store($key, $data);

	/**
	 * @abstract
	 * @param mixed $key
	 * @return array
	 */
	public function load($key = NULL);

	/**
	 * @abstract
	 * @return void
	 */
	public function clear();
}
