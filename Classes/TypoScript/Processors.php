<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\TypoScript;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package TYPO3
 * @subpackage TypoScript
 * @version $Id$
 */

/**
 * A library of standard processors for TypoScript objects. Most of these functions
 * were known as "standard wrap" properties / functions in TYPO3 4.x.
 *
 * @package TYPO3
 * @subpackage TypoScript
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Processors {

	const CROP_FROM_BEGINNING = 1;
	const CROP_AT_WORD = 2;
	const CROP_AT_SENTENCE = 4;

	const SHIFT_CASE_TO_UPPER = 1;
	const SHIFT_CASE_TO_LOWER = 2;
	const SHIFT_CASE_TO_TITLE = 4;

	/**
	 * Crops a part of a string and optionally replaces the cropped part by a string, typically
	 * three dots ("...").
	 *
	 * The third parameter is a bitmask combination of the CROP_* constants:
	 *
	 *    CROP_FROM_BEGINNING: If set, the beginning of the string will be cropped instead of the end.
	 *    CROP_AT_WORD: The string will be of the maximum length specified by $maximumNumberOfCharacters, but it will be cropped after the last (or before the first) space instead of the probably the middle of a word.
	 *
	 * @param  string				$subject: The string to crop
	 * @param  integer				$maximumNumberOfCharacters: The maximum number of characters to which the subject shall be shortened
	 * @param  string				$preOrSuffixString: A string which is to be prepended or appended to the cropped subject if the subject has been cropped at all.
	 * @param  long					$options: Any combination of the CROP_ constants as a bitmask
	 * @return string				The processed string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function processor_crop($subject, $maximumNumberOfCharacters, $preOrSuffixString = '', $options = 0) {
		if (\F3\PHP6\Functions::strlen($subject) > $maximumNumberOfCharacters) {
			if ($options & self::CROP_FROM_BEGINNING) {
				if ($options & self::CROP_AT_WORD) {
					$iterator = new \F3\PHP6\TextIterator($subject, \F3\PHP6\TextIterator::WORD);
					$processedSubject = \F3\PHP6\Functions::substr($subject, $iterator->following($maximumNumberOfCharacters));
				} else {
					$processedSubject = \F3\PHP6\Functions::substr($subject, $maximumNumberOfCharacters);
				}
				$processedSubject = $preOrSuffixString . $processedSubject;
			} else {
				if ($options & self::CROP_AT_WORD) {
					$iterator = new \F3\PHP6\TextIterator($subject, \F3\PHP6\TextIterator::WORD);
					$processedSubject = \F3\PHP6\Functions::substr($subject, 0, $iterator->preceding($maximumNumberOfCharacters));
				} elseif ($options & self::CROP_AT_SENTENCE) {
					$iterator = new \F3\PHP6\TextIterator($subject, \F3\PHP6\TextIterator::SENTENCE);
					$processedSubject = \F3\PHP6\Functions::substr($subject, 0, $iterator->preceding($maximumNumberOfCharacters));
				} else {
					$processedSubject = \F3\PHP6\Functions::substr($subject, 0, $maximumNumberOfCharacters);
				}
				$processedSubject .= $preOrSuffixString;
			}
		}
		return $processedSubject;
	}

	/**
	 * Wraps the specified string into a prefix- and a suffix string.
	 *
	 * @param  string				$subject: The string to wrap
	 * @param  string				$prefixString: The string to prepend
	 * @param  string				$suffixString: The string to append
	 * @return string				The processed string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function processor_wrap($subject, $prefixString, $suffixString) {
		return $prefixString . $subject . $suffixString;
	}

	/**
	 * Shifts the case of a string into the specified direction.
	 *
	 * @param  string				$subject: The string to change the case for
	 * @param  long					$direction: One of the SHIFT_CASE_* constants
	 * @return string				The processed string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function processor_shiftCase($subject, $direction) {
		switch ($direction) {
			case self::SHIFT_CASE_TO_LOWER :
				$processedSubject = \F3\PHP6\Functions::strtolower($subject);
				break;
			case self::SHIFT_CASE_TO_UPPER :
				$processedSubject = \F3\PHP6\Functions::strtoupper($subject);
				break;
			case self::SHIFT_CASE_TO_TITLE :
				$processedSubject = \F3\PHP6\Functions::strtotitle($subject);
				break;
			default:
				throw new \F3\TypoScript\Exception('Invalid direction specified for case shift. Please use one of the SHIFT_CASE_* constants.', 1179399480);
		}
		return $processedSubject;
	}

	/**
	 * Transforms an UNIX timestamp according to the given format. For the possible format values, look at the php date() function.
	 *
	 * @param  string				$subject: The UNIX timestamp to transform
	 * @param  long					$format: A format string, according to the rules of the php date() function
	 * @return string				The transformed date string
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function processor_date($subject, $format) {
		if ($subject === '') return '';

		$timestamp = is_object($subject) ? (string)$subject : $subject;
		$format = (string)$format;
		if ($timestamp <= 0) throw new \F3\TypoScript\Exception('The given timestamp value was zero or negative, sorry this is not allowed.', 1185282371);

		return date($format, $timestamp);
	}

	/**
	 * Overrides the current subject with the given value, if the value is not empty.
	 *
	 * @param  string				$subject: The current subject in the processor chain
	 * @param  string				$replacement: The value that overrides the subject
	 * @return string				The new subject
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function processor_override($subject, $replacement) {
		$replacementIsEmpty = (trim((string)$replacement) === '' || trim((string)$replacement) === '0');
		return $replacementIsEmpty ? $subject : $replacement;
	}

	/**
	 * Overrides the current subject with the given value, if the subject (trimmed) is empty.
	 *
	 * @param  string				$subject: The current subject in the processor chain
	 * @param  string				$replacement: The value that overrides the subject
	 * @return string				The new subject
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function processor_ifEmpty($subject, $replacement) {
		$subjectIsEmpty = (trim((string)$subject) === '' || trim((string)$subject) === '0');
		return $subjectIsEmpty ? $replacement : $subject;
	}

	/**
	 * Overrides the current subject with the given value, if the subject (not trimmed) is empty.
	 *
	 * @param  string				$subject: The current subject in the processor chain
	 * @param  string				$replacement: The value that overrides the subject
	 * @return string				The new subject
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function processor_ifBlank($subject, $replacement) {
		return (!\F3\PHP6\Functions::strlen((string)$subject)) ? $replacement : $subject;
	}

	/**
	 * Trims the current subject (Removes whitespaces arround the value).
	 *
	 * @param  string				$subject: The current subject in the processor chain
	 * @return string				The new trimmed subject
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function processor_trim($subject) {
		return trim((string)$subject);
	}

	/**
	 * Returns the trueValue when the condition evaluates to TRUE, otherwise
	 * the falseValue is returned.
	 *
	 * If the condition is a TypoScript object, it is handled as a string.
	 *
	 * The following conditions are considered TRUE:
	 *
	 *   - boolean TRUE
	 *   - number > 0
	 *   - non-empty string
	 *
	 * While these conditions evaluate to FALSE:
	 *
	 *   - boolean FALSE
	 *   - number <= 0
	 *   - empty string
	 *
	 * @param  string				$subject: The current subject in the processor chain
	 * @param  boolean				$condition: The condition for the if clause, or simply TRUE/FALSE
	 * @param  string				$trueValue: This is returned if $condition is TRUE
	 * @param  string				$falseValue: This is returned if $condition is FALSE
	 * @return mixed				The calculated return value. Either $trueValue or $falseValue
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function processor_if($subject, $condition, $trueValue, $falseValue) {
		if (!is_bool($condition)) {
			if (is_object($condition)) $condition = (string)$condition;
			if ((is_numeric($condition) && $condition <= 0) || $condition === '') $condition = FALSE;
			if ($condition === 1 || (is_string($condition) && \F3\PHP6\Functions::strlen($condition) > 0)) $condition = TRUE;
		}
		if (!is_bool($condition)) throw new \F3\TypoScript\Exception('The condition in the if processor could not be converted to boolean. Got: (' . gettype($condition) . ')' . (string)$condition, 1185355020);

		return ($condition ? $trueValue : $falseValue);
	}

	/**
	 * Returns TRUE, if the subject (trimmed) is empty.
	 *
	 * @param  string				$subject: The current subject in the processor chain
	 * @return boolean				The calculated return value. Either TRUE or FALSE
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function processor_isEmpty($subject) {
		return (trim((string)$subject) === '' || trim((string)$subject) === '0');
	}

	/**
	 * Returns TRUE, if the subject (not trimmed) is blank.
	 *
	 * @param  string				$subject: The current subject in the processor chain
	 * @return boolean				TRUE if the subject is blank, otherwise FALSE
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function processor_isBlank($subject) {
		return (!\F3\PHP6\Functions::strlen((string)$subject));
	}

	/**
	 * Returns a substring.
	 *
	 * @param string $subject The current subject in the processor chain
	 * @param integer $start The left boundary of the substring
	 * @param integer $length The length of the substring
	 * @return string The substring
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function processor_substring($subject, $start, $length = NULL) {
		if (!is_integer($start)) throw new \F3\TypoScript\Exception('Expected an integer as start position, ' . gettype($start) . ' given.', 1224003810);
		if ($length !== NULL && !is_integer($length)) throw new \F3\TypoScript\Exception('Expected an integer as length, ' . gettype($length) . ' given.', 1224003811);

		return \F3\PHP6\Functions::substr((string)$subject, $start, $length);
	}

	/**
	 * Converts the subject into an integer.
	 *
	 * @param string $subject The current subject in the processor chain
	 * @return integer The integer value
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function processor_toInteger($subject) {
		return intval((string)$subject);
	}
	
	/**
	 * Rounds a given float value. If integer given, nothing happens.
	 *
	 * @param float/string $subject The subject to round.
	 * @param integer $precision Number of digits after the decimal point. Negative values are also supported. (-1 rounds to full 10ths)
	 * @return float Rounded value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function processor_round($subject, $precision = NULL) {
		if (!is_numeric($subject)) throw new \F3\TypoScript\Exception('Expected an integer or float passed, ' . gettype($subject) . ' given.', 1224053300);
		$subject = floatval($subject);
		if ($precision != NULL && !is_int($precision)) throw new \F3\TypoScript\Exception('Precision must be an integer.');
		return round($subject, $precision);
	}
	
	/**
	 * Multiplies a given number or numeric string $subject with $factor.
	 * 
	 * @param float/string $subject The first factor
	 * @param float $factor The second factor
	 * @return float $subject*$factor
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function processor_multiply($subject, $factor) {
		if (!is_numeric($subject)) throw new \F3\TypoScript\Exception('Expected a numeric string as first parameter.', 1224146988);
		if (!is_float($factor) && !is_int($factor)) throw new \F3\TypoScript\Exception('Expected a float as second parameter.', 1224146995);
		$subject = floatval($subject);
		return $subject*$factor;
	}
}
?>