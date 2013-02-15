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
class Tx_LwEnetMultipleActionForms_Utility_Controller {

	/**
	 * Initializes the autoload mechanism of Extbase. This is supplement to the core autoloader.
	 *
	 * @return void
	 * @see initialize()
	 */
	protected static function initializeClassLoader() {
		if (!class_exists('Tx_Extbase_Utility_ClassLoader', FALSE)) {
			require(t3lib_extmgm::extPath('extbase') . 'Classes/Utility/ClassLoader.php');
		}
		$classLoader = new Tx_Extbase_Utility_ClassLoader();
		spl_autoload_register(array($classLoader, 'loadClass'));
		return $classLoader;
	}

	/**
	 * @static
	 * @param array $controllerClassNames
	 * @return array
	 */
	public static function getControllerActions($controllerClassNames) {
		/** @var $classLoader Tx_Extbase_Utility_ClassLoader */
		$classLoader = self::initializeClassLoader();
		$classLoader->loadClass('Tx_LwEnetMultipleActionForms_MVC_Controller_AbstractController');

		$actions = array();
		foreach ($controllerClassNames as $controllerClassName) {
			$classLoader->loadClass($controllerClassName);
			/** @var $classReflection Tx_Extbase_Reflection_ClassReflection */
			$controllerReflection = t3lib_div::makeInstance(
				'Tx_Extbase_Reflection_ClassReflection',
				$controllerClassName
			);
			if ($controllerReflection->isTaggedWith('actionSequence')) {
					// Get action sequence from first annotation
				$actionSequenceAnnotations = $controllerReflection->getTagValues('actionSequence');
				$actionNames = t3lib_div::trimExplode(
					',',
					array_shift($actionSequenceAnnotations),
					TRUE
				);

				if ($controllerReflection->isTaggedWith('additionalActions')) {
						// Get additional actions from first annotation
					$additionalActionAnnotations = $controllerReflection->getTagValues('additionalActions');
					$additionalActionNames = t3lib_div::trimExplode(
						',',
						array_shift($additionalActionAnnotations),
						TRUE
					);
					$actionNames = array_merge(
						$actionNames,
						$additionalActionNames
					);
				}

				$actions[$controllerClassName] = implode(',', $actionNames);
			}
		}
		return $actions;
	}

	/**
	 * @static
	 * @param array $classes
	 * @return void
	 */
	protected static function loadClasses(array $classes) {
		foreach ($classes as $className => $classPath) {
			if (!class_exists($className)) {
				require_once($classPath);
			}
		}
	}
}
?>