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
class Tx_LwEnetMultipleActionForms_Utility_Annotations_ValidatorParser {

	/**
	 * Match validator names and options
	 * @var string
	 */
	const PATTERN_MATCH_VALIDATORS = '/
			(?:^|,\s*)
			(?P<validatorName>[a-z0-9_]+)
			\s*
			(?:\(
				(?P<validatorOptions>(?:\s*[a-z0-9]+\s*=\s*(?:
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
	const PATTERN_MATCH_VALIDATOR_OPTIONS = '/
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
	 * @param array $validatorAnnotations
	 * @return array
	 */
	public static function parseValidators(array $validatorAnnotations) {
		$validatorConfigurations = array();
		foreach ($validatorAnnotations as $validatorAnnotation) {
			$validatorConfigurations[] = self::parseValidatorAnnotation($validatorAnnotation);
		}
		return $validatorConfigurations;
	}

	/**
	 * Parses the validator options given in @validate annotations.
	 *
	 * @param string $validateValue
	 * @return array
	 */
	public static function parseValidatorAnnotation($validateValue) {
		$matches = array();
		$validatorConfiguration = array();
		if ($validateValue[0] === '$') {
			$parts = explode(' ', $validateValue, 2);
			$validatorConfiguration['argumentName'] = ltrim($parts[0], '$');
			preg_match_all(
				self::PATTERN_MATCH_VALIDATORS,
				$parts[1],
				$matches,
				PREG_SET_ORDER
			);
		} else {
			preg_match_all(
				self::PATTERN_MATCH_VALIDATORS,
				$validateValue,
				$matches,
				PREG_SET_ORDER
			);
		}
		$match = array_shift($matches);

		$validatorConfiguration['validatorName'] = $match['validatorName'];
		if (isset($match['validatorOptions'])) {
			$validatorConfiguration['validatorOptions'] = self::parseValidatorOptions($match['validatorOptions']);
		}

		return $validatorConfiguration;
	}

	/**
	 * Parses $rawValidatorOptions not containing quoted option values.
	 * $rawValidatorOptions will be an empty string afterwards (pass by ref!).
	 *
	 * @param string &$rawValidatorOptions
	 * @return array An array of optionName/optionValue pairs
	 */
	protected static function parseValidatorOptions($rawValidatorOptions) {
		$validatorOptions = array();
		$parsedValidatorOptions = array();
		preg_match_all(
			self::PATTERN_MATCH_VALIDATOR_OPTIONS,
			$rawValidatorOptions,
			$validatorOptions,
			PREG_SET_ORDER
		);
		foreach ($validatorOptions as $validatorOption) {
			$parsedValidatorOptions[trim($validatorOption['optionName'])] = trim($validatorOption['optionValue']);
		}
		array_walk(
			$parsedValidatorOptions,
			array('Tx_LwEnetMultipleActionForms_Utility_Annotations_ValidatorParser', 'unquoteString')
		);
		return $parsedValidatorOptions;
	}

	/**
	 * Removes escapings from a given argument string and trims the outermost
	 * quotes.
	 *
	 * This method is meant as a helper for regular expression results.
	 *
	 * @param string &$quotedValue Value to unquote
	 */
	protected static function unquoteString(&$quotedValue) {
		switch ($quotedValue[0]) {
			case '"':
				$quotedValue = str_replace('\"', '"', trim($quotedValue, '"'));
			break;
			case '\'':
				$quotedValue = str_replace('\\\'', '\'', trim($quotedValue, '\''));
			break;
		}
		$quotedValue = str_replace('\\\\', '\\', $quotedValue);
	}

}
?>