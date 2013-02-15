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
 * @subpackage Utility|Annotations
 *
 */
class Tx_LwEnetMultipleActionForms_Utility_Annotations_ConstraintParser {

	/**
	 * Match validator names and options
	 * @var string
	 */
	const PATTERN_MATCH_CONSTRAINTS = '/
			(?:^|,\s*)
			(?P<constraintName>[a-z0-9_]+)
			\s*
			(?:\(
				(?P<constraintOptions>(?:\s*[a-z0-9]+\s*=\s*(?:
					"(?:\\\\"|[^"])*"
					|\'(?:\\\\\'|[^\'])*\'
					|(?:\s|[^,"\']*)
				)(?:\s|,)*)*)
			\))?
		/ixS';

	/**
	 * Match validator options (to parse actual options)
	 * @var string
	 */
	const PATTERN_MATCH_CONSTRAINT_OPTIONS = '/
			\s*
			(?P<optionName>[a-z0-9]+)
			\s*=\s*
			(?P<optionValue>
				"(?:\\\\"|[^"])*"
				|\'(?:\\\\\'|[^\'])*\'
				|(?:\s|[^,"\']*)
			)
		/ixS';

	/**
	 * Parses the validator options given in @validate annotations.
	 *
	 * @param array $constraintAnnotations
	 * @return array
	 */
	public static function parseConstraintAnnotations(array $constraintAnnotations) {
		$constraintConfigurations = array();
		foreach ($constraintAnnotations as $constraintAnnotation) {
			$constraintConfigurations[] = self::parseConstraint($constraintAnnotation);
		}
		return $constraintConfigurations;
	}

	/**
	 * Parses the validator options given in @validate annotations.
	 *
	 * @param string $constraint
	 * @return array
	 */
	public static function parseConstraint($constraint) {
		$matches = array();
		if ($constraint[0] === '$') {
			$parts = t3lib_div::trimExplode(' ', $constraint, FALSE, 2);
			$constraintConfiguration = array(
				'constraintProperty' => ltrim($parts[0], '$'),
			);
			if (strpos($parts[1], '->')) {
				$parts = t3lib_div::trimExplode('->', $parts[1]);
				$constraintAnnotation = $parts[0];
					// @todo: create regex to split validators
				$validators = t3lib_div::trimExplode('|', $parts[1]);
				$parsedValidators = Tx_LwEnetMultipleActionForms_Utility_Annotations_ValidatorParser::parseValidators($validators);
				$constraintConfiguration['constraintValidators'] = $parsedValidators;
			} else {
				$constraintAnnotation = $parts[1];
			}
			preg_match_all(
				self::PATTERN_MATCH_CONSTRAINTS,
				$constraintAnnotation,
				$matches,
				PREG_SET_ORDER
			);
		} else {
			$constraintConfiguration = array();
			preg_match_all(
				self::PATTERN_MATCH_CONSTRAINTS,
				$constraint,
				$matches,
				PREG_SET_ORDER
			);
		}

		$match = array_shift($matches);
		$constraintConfiguration['constraintName'] = $match['constraintName'];
		if (isset($match['constraintOptions'])) {
			$constraintConfiguration['constraintOptions'] = self::parseConstraintOptions(
				$match['constraintOptions']
			);
		}

		return $constraintConfiguration;
	}

	/**
	 * Parses $rawValidatorOptions not containing quoted option values.
	 * $rawValidatorOptions will be an empty string afterwards (pass by ref!).
	 *
	 * @param string $rawConstraintOptions
	 * @return array An array of optionName/optionValue pairs
	 */
	protected static function parseConstraintOptions($rawConstraintOptions) {
		$options = array();
		$parsedOptions = array();
		preg_match_all(
			self::PATTERN_MATCH_CONSTRAINT_OPTIONS,
			$rawConstraintOptions,
			$options,
			PREG_SET_ORDER
		);
		foreach ($options as $option) {
			$parsedOptions[trim($option['optionName'])] = trim($option['optionValue']);
		}
		array_walk(
			$parsedOptions,
			array('Tx_LwEnetMultipleActionForms_Utility_String', 'unquoteString')
		);
		return $parsedOptions;
	}

}
?>