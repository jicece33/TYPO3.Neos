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
 * A TypoScript File object
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 */
class File extends \F3\TypoScript\AbstractContentObject {

	/**
	 * Path and filename of the file this TS object is representing.
	 * @var string
	 */
	protected $pathAndFilename;

	/**
	 * Sets the ath and filename of the file this TS object is representing.
	 *
	 * @param string $pathAndFilename
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setPathAndFilename($pathAndFilename) {
		$this->pathAndFilename = $pathAndFilename;
	}

	/**
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPathAndFilename() {
		return $this->pathAndFilename;
	}

	/**
	 * Returns the rendered content of this content object
	 *
	 * @param \F3\TypoScript\RenderingContext $renderingContext
	 * @return string The rendered content as a string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function render(\F3\TypoScript\RenderingContext $renderingContext) {
		if (file_exists($this->pathAndFilename)) {
			return file_get_contents($this->pathAndFilename);
		} else {
			return 'WARNING: File "' . $this->pathAndFilename . '" not found.';
		}
	}

}
?>