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
class Tx_LwEnetMultipleActionForms_Utility_AnnotationParser {

	/**
	 * Match validator names and options
	 * @var string
	 */
	const PATTERN_MATCH_CLASS = '/
			(?:^|,\s*)
			(?P<className>[a-z0-9_]+)
			\s*
			(?:\(
				(?P<options>(?:\s*[a-z0-9]+\s*=\s*(?:
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
	const PATTERN_MATCH_OPTIONS = '/
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
	 * @param array $annotations
	 * @return array
	 */
	public static function parseAnnotations(array $annotations) {
		$annotationConfigurations = array();
		foreach ($annotations as $annotation) {
			$annotationConfigurations[] = self::parseAnnotation($annotation);
		}
		return $annotationConfigurations;
	}

	/**
	 * Parses the validator options given in @validate annotations.
	 *
	 * @param string $annotation
	 * @return array
	 */
	public static function parseAnnotation($annotation) {
		$matches = array();
		if ($annotation[0] === '$') {
			$parts = t3lib_div::trimExplode(' ', $annotation, FALSE, 2);
			$annotationConfiguration = array(
				'argumentName' => ltrim($parts[0], '$'),
			);
			if (strpos($parts[1], '->')) {
				$parts = t3lib_div::trimExplode('->', $parts[1]);
				$classAnnotation = $parts[0];
				$annotationConfiguration['validatorAnnotations'] = t3lib_div::trimExplode(',', $parts[1]);
			} else {
				$classAnnotation = $parts[1];
			}
			preg_match_all(
				self::PATTERN_MATCH_CLASS,
				$classAnnotation,
				$matches,
				PREG_SET_ORDER
			);
		} else {
			$annotationConfiguration = array();
			preg_match_all(
				self::PATTERN_MATCH_CLASS,
				$annotation,
				$matches,
				PREG_SET_ORDER
			);
		}

		foreach ($matches as $match) {
			if (isset($match['options'])) {
				$annotationConfiguration['options'] = self::parseOptions($match['options']);
			}
			$annotationConfiguration['className'] = $match['className'];
		}
		return $annotationConfiguration;
	}

	/**
	 * Parses $rawValidatorOptions not containing quoted option values.
	 * $rawValidatorOptions will be an empty string afterwards (pass by ref!).
	 *
	 * @param string &$rawValidatorOptions
	 * @return array An array of optionName/optionValue pairs
	 */
	protected static function parseOptions($rawOptions) {
		$options = array();
		$parsedOptions = array();
		preg_match_all(
			self::PATTERN_MATCH_OPTIONS	,
			$rawOptions,
			$options,
			PREG_SET_ORDER
		);
		foreach ($options as $option) {
			$parsedOptions[trim($option['optionName'])] = trim($option['optionValue']);
		}
		array_walk(
			$parsedOptions,
			array('Tx_LwEnetMultipleActionForms_Utility_AnnotationParser', 'unquoteString')
		);
		return $parsedOptions;
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